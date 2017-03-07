<?php
/**
 *
 */
class Cataviz
{
  /** Connexion à la base de données */
  private $pdo;
  /** Same persark, cache query */
  private $persark;
  /** Person row */
  private $person;
  /** Graphe, nombre de personnes par document */
  private $persByDocLimit = 5;
  /** Type de rôles */
  public static $roles = array(
    "edition" => "( 360, 540, 550 )",
    "traduction" => "( 680 )",
    "spectacle" => "( 1010, 1011, 1013, 1017, 1018, 1020, 1050, 1060, 1080, 1090 )",
    "musique" => "(220, 221, 222, 223, 510, 1030, 1031, 1033, 1039, 1040, 1100, 1101, 1103, 1108, 1120, 1129, 1130, 1139, 1140, 1149, 1150, 1159, 1160, 1169, 1170, 1179, 1180, 1189, 1190, 1197, 1199, 1200, 1210, 1217, 1218, 1219, 1220, 1229, 1230, 1239, 1240, 1249, 1250, 1257, 1258, 1260, 1268, 1270, 1277, 1278, 1280, 1287, 1288, 1289, 1290, 1299, 1300, 1309, 1310, 1317, 1318, 1320, 1330, 1337, 1340, 1350, 1357, 1358, 1360, 1367, 1368, 1370, 1377, 1378, 1380, 1387, 1388, 1389, 1390, 1400, 1407, 1410, 1418, 1420, 1427, 1428, 1430, 1437, 1438, 1440, 1450, 1459, 1460, 1470, 1477, 1478, 1480, 1490, 1500, 1510, 1520, 1527, 1530, 1537, 1540, 1550, 1557, 1558, 1560, 1567, 1569, 1570, 1580, 1587, 1590, 1597, 1598, 1599, 1600, 1607, 1610, 1620, 1630, 1637, 1638, 1640, 1649, 1650, 1651, 1653, 1657, 1658, 1659, 1660, 1667, 1668, 1670, 1680, 1688, 1690, 1700, 1707, 1710, 1717, 1718, 1720, 1728, 1730, 1738, 1740, 1747, 1748, 1750, 1760, 1767, 1770, 1777, 1780, 1787, 1790, 1797, 1798, 1800, 1807, 1810, 1817, 1818, 1820, 1827, 1828, 1830, 1837, 1840, 1850, 1860, 1870, 1878, 1880, 1888, 1890, 1898, 1900, 1910, 1920, 1930, 1937, 1938, 1940, 1947, 1948)",
    "illustration" => "( 440, 520, 521, 522, 523, 524, 530, 531, 532, 533, 534 )",
    "auteur" => "( 70, 71, 72, 73, 980, 990 )",
  );
  /* intitulés pour les types de documents */
  public static $types = array(
    "Archive" => "[archive]",
    "Image" => "[image]",
    "InteractiveResource" => "[autre]",
    "MovingImage" => "[film]",
    "PhysicalObject" => "[objet]",
    "Score" => "[partition]",
    "Sound" => "[son]",
    "StillImage" => "[image]",
    "Text" => "[texte]",
  );
  /** Rôles principaux */
  public static $creator = array(
    70 => "Auteur",
    71 => "Auteur présumé",
    72 => "Auteur adapté",
    73 => "Auteur prétendu",
    980 => "Auteur",
    990 => "Auteur",
    4020 => "Auteur de l’envoi",
  );

  /**
   *
   */
  function __construct( $sqlfile="databnf.db" )
  {
    $this->pdo = new PDO( 'sqlite:'.$sqlfile );
    $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
    $this->pdo->exec("pragma synchronous = off;");
  }
  /**
   * Renvoyer un pdo::statement
   */
  function prepare( $sql ) {
    return $this->pdo->prepare( $sql );
  }
  /**
   * Renvoyer les informations sur une personne, met en cache le résultat
   */
  function person( $persark=null )
  {
    if ( !$persark ) return $this->person;
    if ( $persark == $this->persark ) return $this->person;
    $this->$persark = $persark;
    $this->person = $this->pdo->query( "SELECT * FROM person WHERE ark = ".$this->pdo->quote( $persark ) )->fetch( PDO::FETCH_ASSOC );
    return $this->person;
  }
  /**
   * Met en forme un nom de personne avec un rang de la base de données
   */
  function perstitle( $persark )
  {
    $person = $this->person( $persark );
    $html = array();
    $html[] = $person['name'];
    if ( $person['deathyear'] < 0 ) $html[] = " (".$person['birthyear']."/".$person['deathyear'].")";
    else if ( $person['deathyear'] > 0 ) $html[] = " (".$person['birthyear']."–".$person['deathyear'].")";
    else if ( $person['birthyear'] > 0 ) $html[] = " (".$person['birthyear']."–…)";
    return implode( "", $html );
  }

  /**
   * Nombre de documents relatifs à un auteur
   */
  function dygraph( $persark )
  {
    $person = $this->person( $persark );
    $csv = array();
    $from = $person['birthyear'];
    if (!$from ) $from = 1400; // ex : Homère
    if ($from < 1400) $from = 1450;
    $to = 2015;
    $sql = "SELECT count(*) FROM contribution WHERE person = ? AND date = ? AND writes = 1";
    $q = $this->pdo->prepare( $sql );
    // collecter toute la série pour calculer ensuite la moyenne glissante;
    $years = array();
    $counts = array();
    for ( $date=$from; $date<=$to; $date++ ) {
      $years[] = $date;
      $q->execute( array( $person['id'], $date ) );
      $counts[] = current( $q->fetch( PDO::FETCH_NUM ) );
    }
    // sortie tableau js
    $txt = array();
    $txt[] = "[";
    $size = count( $counts );
    // durée de vie d’un livre
    $long = 30;
    $time = microtime( true );
    for ( $i=0; $i < $size; $i++ ) {
      $ifrom = max(0, $i-$long);
      $stock = 0;
      for ( $j = $ifrom; $j <= $i; $j++) {
        // echo $counts[$j].' '.( ( $long - ( $i-$j ) ) / $long ).', ';
        // un livre en fin de vie ne vaut plus rien
        $stock += $counts[$j] * ( ( $long - ( $i-$j ) ) / $long );
      }
      // $avg = number_format( array_sum( array_slice( $counts, $ifrom, $iwidth ) ) / $iwidth, 1, '.', '' );

      $txt[] = '   ['.$years[$i].','.$counts[$i].','.$stock.'],';
    }
    $txt[] = "]";
    return implode("\n", $txt);
  }

  /**
   * Lister les collaborations comme un tableau
   */

  /**
   * Réseau des personnes
   * — sélectionnner les documents liés à l’auteur pivot, filtrer par dates
   * — sélectionner les autres contributeurs à ces documents, filtrer par rôle
   * — écrire les relations
   * — retenir les documents
   * — retenir les contributeurs
   * — écrire les nœuds
   */
  function sigma( $persark, $from, $to, $role=null )
  {
    // personne au centre
    $center = $this->person( $persark );
    // les données à retourner
    $json = array();
    $json[] = '{';
    // sélectionner tous les documents liés à cet auteur personne
    // chercher un empan de dates convainquant
    $from = 0 + $from;
    $to = 0 + $to;
    if ( !$from ) $from = $center['birthyear'];
    if ( !$to && $center['deathyear'] ) $to = $center['deathyear'];
    else if (!$to) $to = 2016;
    if ( $from > $to ) $to = $from;
    $qdoc = $this->pdo->prepare( "
      SELECT document.*
        FROM contribution, document
        WHERE contribution.document=document.id AND person = ? AND contribution.date >=? AND contribution.date <=?
        ORDER BY date
    " ); // vérifier si ce tri ne fait pas trop perdre de temps
    $qdoc->execute( array( $center['id'], $from, $to ) );

    $filter = "";
    if ( isset( self::$roles[$role] ) )  $filter=" AND role IN ".self::$roles[$role];
    $sql = "SELECT person.*, contribution.role
        FROM contribution, person
        WHERE contribution.person=person.id AND document = ? $filter
        LIMIT ?
    ";

    $qpers = $this->pdo->prepare( $sql ); // filtrer sur le rôle ?
    $datemin=2016;
    $datemax=0;
    // collecter la liste des documents et des personnes
    $document = array();
    $person = array();
    $json[] = '  "edges": [';
    $edgeid = 1;
    while ( $doc = $qdoc->fetch( PDO::FETCH_ASSOC ) ) {
      // tester si ce document à un autre contributeur avant de stocker
      $qpers->execute( array( $doc['id'], 5 ) );
      while ( $pers = $qpers->fetch( PDO::FETCH_ASSOC ) ) {
        // écrire la relation à la personne centrale
        $json[] = '      { "id":'.$edgeid.', "source":"'.$center['ark'].'", "target":"'.$doc['ark'].'", "color":"#CCCCCC" },';
        $edgeid++;
        // garder en mémoire le document et la personne
        $document[$doc['id']] = $doc;
        if ( $doc['date'] && $doc['date'] < $datemin ) $datemin = $doc['date'];
        if ( $doc['date'] > $datemax ) $datemax = $doc['date'];
        // augmenter le compteur de la personne
        if ( isset( $person[$pers['id']] ) ) {
          $person[$pers['id']]['size']++;
        }
        else {
          $person[$pers['id']] = $pers;
          $person[$pers['id']]['size'] = 1;
        }
        // écrire la relation
        $color = "#CCCCCC";
        if ( isset( self::$creator[$pers['role']] ) ) $color="#FF0000";
        $json[] = '      { "id":'.$edgeid.', "source":"'.$pers['ark'].'", "target":"'.$doc['ark'].'", "color":"'.$color.'" },';
        $edgeid++;
      }
    }
    // $json_options = JSON_UNESCAPED_UNICODE; // incompatible 5.3
    $json_options = null;
    $json[] = '    ],';
    $json[] = '  "nodes": [';
    foreach ( $document as $docid=>$doc ) {
      $x = mt_rand ( -100, +100 );
      $y = mt_rand ( -100, +100 );
      $label = "";
      if ( $doc['type'] && $doc['type'] != 'Text' ) $label .= self::$types[$doc['type']]." ";
      if ( $doc['date'] ) $label .= ((string)$doc['date']).". ";
      if ( $doc['title'] ) $label .= $doc['title'];
      else $label .= $doc['ark'];
      // if ( mb_strlen( $label ) > 50 ) $label = mb_substr( $label, 0, mb_strpos( $label, ' ', 40 )).' […]';
      $label = json_encode( $label, $json_options );
      $json[] = '      { "type":"document", "id":"'.$doc['ark'].'", "label":'.$label.', "x":'.$x.',  "y":'.$y.', "size":3, "color":"rgba(0, 0, 255, 0.3"},';
    }
    foreach ( $person as $id=>$pers ) {
      $x = mt_rand ( -100, +100 );
      $y = mt_rand ( -100, +100 );
      $size = $pers['size'];
      $label = $pers['name'];
      if ($pers['family']) {
        $label = $pers['family'];
        if ($pers['given']) $label .= ', '.$pers['given'];
      }
      $label = json_encode( $label, $json_options );
      $color = "rgba( 0, 0, 0, 0.3 )";
      $json[] = '      { "type":"person", "id":"'.$pers['ark'].'", "label":'.$label.', "x":'.$x.',  "y":'.$y.', "size":'.$size.', "birth":"'.$pers['birthyear'].'", "death":"'.$pers['deathyear'].'", "color":"'.$color.'"},';
    }
    // poser l’auteur central
    $json[] = '      { "type":"person", "id":"'.$center['ark'].'", "label":"'.$center['name'].'", "x":0,  "y":0, "size":10, "color":"rgba( 255, 0, 0, 0.5 )"}';
    $json[] = '  ]';
    $json[] = '}';
    return implode( "\n", $json);
  }

  /**
   * Chronologie des éditions selon les œuvres
   */
  function editions( $persark )
  {
    $person = $this->person();
    // compter le nombre d’oeuvre avant d’afficher quelque chose
    $q = $this->pdo->prepare( "SELECT count(*) FROM creation WHERE person = ?" );
    $q->execute( array( $person['id'] ) );
    if( !current($q->fetch()) ) return;

    $html = array();
    $html[] = '<p>Cette bibliographie est établie automatiquement à partir des <a href="http://data.bnf.fr/liste-oeuvres">notices d’œuvres</a> de la BNF. Elle peut être significative de l’histoire éditoriale d’un auteur, mais elle ne sera pas exhaustive, ni des œuvres, ni des rééditions.</p>';
    // récupérer le premier document de l’auteur
    $sql = "";

    // prendre
    // boucler sur les œuvres d’un auteur
    $qwork = $this->pdo->prepare( "SELECT work.* FROM creation, work WHERE creation.work = work.id AND person = ? ORDER BY versions DESC;" );
    $qwork->execute( array( $person['id'] ) );

    // boucler sur les éditions de ces œeuvres
    $qdocument =  $this->pdo->prepare( "SELECT document.* FROM version, document WHERE version.document=document.id AND work = ? AND date > 0 " );
    // nombre d’éditions
    $sql = "SELECT count(*) FROM version, document WHERE version.document=document.id AND work = ? AND date > 0 ";
    $qcount =  $this->pdo->prepare( $sql );

    $width = 800;
    $tick = 1;
    $work1 = true;
    $html[] = '<table class="editions sortable" align="center" style="position: relative; ">';
    $html[] = ' <tr>';
    $html[] = '   <td class="nosort" width="'.$width.'">';

    $q = $this->pdo->prepare( "SELECT date FROM contribution WHERE person = ? AND date > 0 ORDER BY date LIMIT 1;" );
    $q->execute( array( $person['id'] ) );
    $from = current( $q->fetch( PDO::FETCH_ASSOC ) );
    $to = 2016;

    $mods = array( 1, 2, 5, 10, 20, 50, 100);
    $mod = 10;
    if ( $to - $from > 300) $mod=20;
    $yearmin = floor( $from / $mod ) * $mod;
    $yearmax = ceil( $to / $mod ) * $mod;
    $tick = $width / ( $yearmax - $yearmin);
    for ( $year=$yearmin; $year <= $yearmax; $year += $mod ) {
      $left = number_format( ($year - $yearmin) * $tick, 1, '.', '' );
      $html[] = '<div class="year" style="left: '.$left.'px">'.$year.'</div>';
      $html[] = '<div class="gridy" style="left: '.$left.'px"></div>';
    }
    $html[] = '   </td>';
    $html[] = '   <th>Éditions</th>';
    $html[] = '   <th>Date</th>';
    $html[] = '   <th>Titre</th>';
    $html[] = ' </tr>';

    while ( $work = $qwork->fetch( PDO::FETCH_ASSOC ) ) {
      $qcount->execute( array( $work['id'] ) );
      $count = current($qcount->fetch());
      if ( !$count ) continue;

      $html[] = '<tr>';
      $html[] = '<td>';
      $qdocument->execute( array( $work['id'] ) );
      while ( $document = $qdocument->fetch( PDO::FETCH_ASSOC ) ) {
        $left = number_format( ($document['date'] - $yearmin) * $tick, 1, '.', '');
        $html[] = '<a target="_new" class="edition" href="http://catalogue.bnf.fr/ark:/12148/'.$document['ark'].'" title="'.$document['date'].', '.htmlspecialchars( $document['title'] ).'" style="left: '.$left.'px;">|</a>';
      }
      $html[] = '</td>';
      $qcount->execute( array( $work['id'] ) );
      $html[] = '<td>'.$count.'</td>';
      $html[] = '<td>'.$work['date'].'</td>';
      $html[] = '<td>'.$work['title'].'</td>';
      $html[] = '</tr>';
    }
    $html[] = '</table>';
    return implode( $html, "\n" );
  }
  /**
   * Les identifiants BNF sont sûrs
   */
   public static function ark2id( $ark ) {
     return 0+substr($ark, 2, -1);
   }

}

?>
