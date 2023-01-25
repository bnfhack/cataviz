<?php
include_once(__DIR__ . '/vendor/autoload.php');


/**
 *
 */
class Cataviz
{
    /** configuration parameters */
    static public $config;
    /** Connexion à la base de données */
    static private $pdo;
    /** parameters */
    static public $p = array(
        'date_min' => 1452,
        'date_max' => 2019,
    );


    /** Same persark, cache query */
    private $persark;
    /** Person row */
    private $person;
    /** Graphe, nombre de personnes par document */
    private $persByDocLimit = 5;


    /**
     * init static props
     */
    static function init()
    {
        $config_file = __DIR__ . '/config.php';
        // help installation
        if (!is_file($config_file)) {
            copy ( __DIR__ . '/_config.php', $config_file);
        }
        self::$config = include($config_file);
        set_time_limit(-1);
        ini_set('display_errors', 1);
        error_reporting(E_ALL);
        // Some default values for this installation
        self::$p['from'] = 1685;
        self::$p['to'] = 1913;
        Cataviz::pars();
        self::connect(self::$config['db_file']);
    }

    /**
     *
     */
    static function connect($cataviz_db)
    {
        if (!file_exists($cataviz_db)) exit(
            "Impossible to connect to " . $cataviz_db
        );
        self::$pdo = new PDO(
            'sqlite:' . $cataviz_db,
            null,
            null,
            array(PDO::ATTR_PERSISTENT => true)
        );
        self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        self::$pdo->exec("pragma synchronous = off;");
        self::$pdo->exec("pragma journal_mode=MEMORY;");
    }

    /**
     * Get parameters
     */
    static function pars()
    {
        $from = null;
        if (isset($_REQUEST['from']) &&  is_numeric($_REQUEST['from'])) {
            $from = $_REQUEST['from'];
            if ($from < self::$p['date_min']) $from = self::$p['date_min'];
            if ($from > self::$p['date_max']) $from = null;
        }
        if ($from !== null) {
            self::$p['from'] = $from;
        }
        $to = null;
        if (isset($_REQUEST['to']) &&  is_numeric($_REQUEST['to'])) {
            $to = $_REQUEST['to'];
            if ($to < self::$p['date_min']) $to = null;
            if ($to > self::$p['date_max']) $to = self::$p['date_max'];
        }
        if ($to !== null) {
            self::$p['to'] = $to;
        }


        /*
        $pagefloor = 50;
        if (isset($_REQUEST['pagefloor'])) $pagefloor = $_REQUEST['pagefloor'];

        if (!isset($datemax)) $datemax = 2016;
        if (!isset($from)) $from = 1900;
        if (!isset($to)) $to = $datemax;
        if (!isset($smooth)) $smooth = 0;
        
        
        if ($from < 1452) $from = 1452;
        if ($from > $datemax) $from = $datemax;
        
        if (isset($_REQUEST['to']) && is_numeric($_REQUEST['to'])) $to = $_REQUEST['to'];
        if ($to < 1475) $to = $datemax;
        if ($to > $datemax) $to = $datemax;
        
        if (isset($_REQUEST['smooth']) && is_numeric($_REQUEST['smooth'])) $smooth = $_REQUEST['smooth'];
        if ($smooth < 0) $smooth = 0;
        if ($smooth > 50) $smooth = 50;
        
        if (!isset($log))  $log = 0;
        if (isset($_REQUEST['log'])) $log = $_REQUEST['log'];
        if (!$log) $log = 0;
        */
    }

    /**
     * Renvoyer un pdo::statement
     */
    static public function prepare($sql)
    {
        return self::$pdo->prepare($sql);
    }
    /**
     * Renvoyer les informations sur une personne, met en cache le résultat
     */
    static function person($persark = null)
    {
        if (!$persark) return $this->person;
        if ($persark == $this->persark) return $this->person;
        $this->$persark = $persark;
        $this->person = self::$pdo->query("SELECT * FROM person WHERE ark = " . self::$pdo->quote($persark))->fetch(PDO::FETCH_ASSOC);
        return $this->person;
    }
    /**
     * Met en forme un nom de personne avec un rang de la base de données
     */
    static function perstitle($persark)
    {
        $person = $this->person($persark);
        $html = array();
        $html[] = $person['family'];
        if ($person['given']) $html[] = ", " . $person['given'];
        if ($person['deathyear'] < 0) $html[] = " (" . $person['birthyear'] . "/" . $person['deathyear'] . ")";
        else if ($person['deathyear'] > 0) $html[] = " (" . $person['birthyear'] . "–" . $person['deathyear'] . ")";
        else if ($person['birthyear'] > 0) $html[] = " (" . $person['birthyear'] . "–…)";
        return implode("", $html);
    }

    /**
     * Nombre de documents relatifs à un auteur
     */
    static function dygraph($persark)
    {
        $person = $this->person($persark);
        $csv = array();
        $from = $person['birthyear'];
        if (!$from) $from = 1400; // ex : Homère
        if ($from < 1400) $from = 1450;
        $to = 2016;
        $sql = "SELECT count(*) FROM contribution WHERE person = ? AND date = ? AND writes = 1";
        $q = self::$pdo->prepare($sql);
        // collecter toute la série pour calculer ensuite la moyenne glissante;
        $years = array();
        $counts = array();
        for ($date = $from; $date <= $to; $date++) {
            $years[] = $date;
            $q->execute(array($person['id'], $date));
            $counts[] = current($q->fetch(PDO::FETCH_NUM));
        }
        // sortie tableau js
        $txt = array();
        $txt[] = "[";
        $size = count($counts);
        // durée de vie d’un livre
        $long = 30;
        $time = microtime(true);
        for ($i = 0; $i < $size; $i++) {
            $ifrom = max(0, $i - $long);
            $stock = 0;
            for ($j = $ifrom; $j <= $i; $j++) {
                // echo $counts[$j].' '.(($long - ($i-$j)) / $long).', ';
                // un livre en fin de vie ne vaut plus rien
                $stock += $counts[$j] * (($long - ($i - $j)) / $long);
            }
            // $avg = number_format(array_sum(array_slice($counts, $ifrom, $iwidth)) / $iwidth, 1, '.', '');

            $txt[] = '   [' . $years[$i] . ',' . $counts[$i] . ',' . $stock . '],';
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
    static function sigma($persark, $from, $to, $role = null)
    {
        // personne au centre
        $center = $this->person($persark);
        // les données à retourner
        $json = array();
        $json[] = '{';
        // sélectionner tous les documents liés à cet auteur personne
        // chercher un empan de dates convainquant
        $from = 0 + $from;
        $to = 0 + $to;
        if (!$from) $from = $center['birthyear'];
        if (!$to && $center['deathyear']) $to = $center['deathyear'];
        else if (!$to) $to = 2016;
        if ($from > $to) $to = $from;
        $qdoc = self::$pdo->prepare("
      SELECT document.*
        FROM contribution, document
        WHERE contribution.document=document.id AND person = ? AND contribution.date >=? AND contribution.date <=?
        ORDER BY date
    "); // vérifier si ce tri ne fait pas trop perdre de temps
        $qdoc->execute(array($center['id'], $from, $to));

        $filter = "";
        if (isset(self::$roles[$role]))  $filter = " AND role IN " . self::$roles[$role];
        $sql = "SELECT person.*, contribution.role
        FROM contribution, person
        WHERE contribution.person=person.id AND person != ? AND document = ? $filter
        LIMIT ?
    ";
        $qpers = self::$pdo->prepare($sql); // filtrer sur le rôle ?
        $datemin = 2016;
        $datemax = 0;
        // collecter la liste des documents et des personnes
        $document = array();
        $person = array();
        $json[] = '  "edges": [';
        $edgeid = 1;
        while ($doc = $qdoc->fetch(PDO::FETCH_ASSOC)) {
            // tester si ce document à un autre contributeur avant de stocker
            $qpers->execute(array($center['id'], $doc['id'], $this->persByDocLimit));
            while ($pers = $qpers->fetch(PDO::FETCH_ASSOC)) {
                // écrire la relation à la personne centrale
                $json[] = '      { "id":' . $edgeid . ', "source":"' . $center['ark'] . '", "target":"' . $doc['ark'] . '", "color":"#CCCCCC" },';
                $edgeid++;
                // garder en mémoire le document et la personne
                $document[$doc['id']] = $doc;
                if ($doc['date'] && $doc['date'] < $datemin) $datemin = $doc['date'];
                if ($doc['date'] > $datemax) $datemax = $doc['date'];
                // augmenter le compteur de la personne
                if (isset($person[$pers['id']])) {
                    $person[$pers['id']]['size']++;
                } else {
                    $person[$pers['id']] = $pers;
                    $person[$pers['id']]['size'] = 1;
                }
                // écrire la relation
                $color = "#CCCCCC";
                if (isset(self::$creator[$pers['role']])) $color = "#FF0000";
                $json[] = '      { "id":' . $edgeid . ', "source":"' . $pers['ark'] . '", "target":"' . $doc['ark'] . '", "color":"' . $color . '" },';
                $edgeid++;
            }
        }
        // $json_options = JSON_UNESCAPED_UNICODE; // incompatible 5.3
        $json_options = null;
        $json[] = '    ],';
        $json[] = '  "nodes": [';
        foreach ($document as $docid => $doc) {
            $x = mt_rand(-100, +100);
            $y = mt_rand(-100, +100);
            $label = "";
            if ($doc['type'] && $doc['type'] != 'Text') $label .= self::$types[$doc['type']] . " ";
            if ($doc['date']) $label .= ((string)$doc['date']) . ". ";
            if ($doc['title']) $label .= $doc['title'];
            else $label .= $doc['ark'];
            // if (mb_strlen($label) > 50) $label = mb_substr($label, 0, mb_strpos($label, ' ', 40)).' […]';
            $label = json_encode($label, $json_options);
            $json[] = '      { "type":"document", "id":"' . $doc['ark'] . '", "label":' . $label . ', "x":' . $x . ',  "y":' . $y . ', "size":3, "color":"rgba(0, 0, 255, 0.3"},';
        }
        foreach ($person as $id => $pers) {
            $x = mt_rand(-100, +100);
            $y = mt_rand(-100, +100);
            $size = $pers['size'];
            $label = $pers['name'];
            if ($pers['family']) {
                $label = $pers['family'];
                if ($pers['given']) $label .= ', ' . $pers['given'];
            }
            $label = json_encode($label, $json_options);
            $color = "rgba(0, 0, 0, 0.3)";
            $json[] = '      { "type":"person", "id":"' . $pers['ark'] . '", "label":' . $label . ', "x":' . $x . ',  "y":' . $y . ', "size":' . $size . ', "birth":"' . $pers['birthyear'] . '", "death":"' . $pers['deathyear'] . '", "color":"' . $color . '"},';
        }
        // poser l’auteur central
        $json[] = '      { "type":"person", "id":"' . $center['ark'] . '", "label":"' . $center['name'] . '", "x":0,  "y":0, "size":10, "color":"rgba(255, 0, 0, 0.5)"}';
        $json[] = '  ]';
        $json[] = '}';
        return implode("\n", $json);
    }

    /**
     * Chronologie des éditions selon les œuvres
     */
    static function editions($persid)
    {
        // compter le nombre d’oeuvre avant d’afficher quelque chose
        $q = self::$pdo->prepare("SELECT count(*) FROM creation WHERE person = ?");
        $q->execute(array($persid));
        if (!current($q->fetch())) return;

        $html = array();
        // récupérer le premier document de l’auteur
        $sql = "";

        // prendre
        // boucler sur les œuvres d’un auteur
        $qwork = self::$pdo->prepare("SELECT work.* FROM creation, work WHERE creation.work = work.id AND person = ? ORDER BY versions DESC;");
        $qwork->execute(array($persid));

        // boucler sur les éditions de ces œeuvres
        $qdocument =  self::$pdo->prepare("SELECT document.* FROM version, document WHERE version.document=document.id AND work = ? AND date > 0 ");
        // nombre d’éditions
        $sql = "SELECT count(*) FROM version, document WHERE version.document=document.id AND work = ? AND date > 0 ";
        $qcount =  self::$pdo->prepare($sql);

        $width = 800;
        $tick = 1;
        $work1 = true;
        $html[] = '<p>Bibliographie établie automatiquement à partir des <a href="http://data.bnf.fr/liste-oeuvres" target="_blank">notices d’œuvres</a> de la BNF. Elle peut être significative de l’histoire éditoriale d’un auteur, mais elle ne sera pas exhaustive, ni des œuvres, ni des rééditions.</p>';
        $html[] = '<table class="editions sortable" align="center" style="position: relative; ">';
        $html[] = ' <tr>';
        $html[] = '   <td class="nosort" width="' . $width . '">';

        $q = self::$pdo->prepare("SELECT date FROM contribution WHERE person = ? AND date > 1482 ORDER BY date LIMIT 1;");
        $q->execute(array($persid));
        $from = current($q->fetch(PDO::FETCH_ASSOC));
        $to = 2016;

        $mods = array(1, 2, 5, 10, 20, 50, 100);
        $mod = 10;
        if ($to - $from > 300) $mod = 20;
        $yearmin = floor($from / $mod) * $mod;
        $yearmax = ceil($to / $mod) * $mod;
        $tick = $width / ($yearmax - $yearmin);
        for ($year = $yearmin; $year <= $yearmax; $year += $mod) {
            $left = number_format(($year - $yearmin) * $tick, 1, '.', '');
            $html[] = '<div class="year" style="left: ' . $left . 'px">' . $year . '</div>';
            $html[] = '<div class="gridy" style="left: ' . $left . 'px"></div>';
        }
        $html[] = '   </td>';
        $html[] = '   <th>Éditions</th>';
        $html[] = '   <th>Date</th>';
        $html[] = '   <th>Titre</th>';
        $html[] = ' </tr>';

        while ($work = $qwork->fetch(PDO::FETCH_ASSOC)) {
            $qcount->execute(array($work['id']));
            $count = current($qcount->fetch());
            if (!$count) continue;

            $html[] = '<tr>';
            $html[] = '<td>';
            $qdocument->execute(array($work['id']));
            while ($document = $qdocument->fetch(PDO::FETCH_ASSOC)) {
                $left = number_format(($document['date'] - $yearmin) * $tick, 1, '.', '');
                $html[] = '<a target="_new" class="edition" href="http://catalogue.bnf.fr/ark:/12148/' . $document['ark'] . '" title="' . $document['date'] . ', ' . htmlspecialchars($document['title']) . '" style="left: ' . $left . 'px;">|</a>';
            }
            $html[] = '</td>';
            $qcount->execute(array($work['id']));
            $html[] = '<td>' . $count . '</td>';
            $html[] = '<td>' . $work['date'] . '</td>';
            $html[] = '<td>' . $work['title'] . '</td>';
            $html[] = '</tr>';
        }
        $html[] = '</table>';
        return implode($html, "\n");
    }
    /**
     * Les identifiants BNF sont sûrs
     */
    public static function ark2id($ark)
    {
        return 0 + substr($ark, 2, -1);
    }
    public static function delta($gender, $date)
    {
        // pas de moyenne pour ces dates
        $guerres = [1914, 1915, 1916, 1917, 1918, 1939, 1940, 1941, 1942, 1943, 1944, 1945];
        $guerres = array_flip($guerres);
        if (isset($guerres[$date])) return 0;
        $revolutions = [1789, 1790, 1791, 1792, 1793, 1794, 1814, 1815, 1830, 1831, 1848, 1870, 1871];
        if ($gender == 1 && isset($revolutions[$date])) return 0;
        if ($date >= 1900) {
            if ($gender == 2) return 3;
            return 1;
        }
        if ($date >= 1789) {
            if ($gender == 2) return 5;
            return 2;
        }
        if ($date >= 1700) {
            if ($gender == 2) return 8;
            return 3;
        }
        if ($date >= 1600) {
            if ($gender == 2) return 10;
            return 4;
        }
        if ($gender == 2) return 12;
        return 5;
    }
}
Cataviz::init();