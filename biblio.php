<?php
include ( dirname(__FILE__).'/Cataviz.php' );
$db = new Cataviz( "databnf.sqlite" );
$limit = @$_REQUEST['limit'];
if ( $limit < 10 ) $limit = 2000;
$name=@$_REQUEST['name'];
$persark=@$_REQUEST['persark'];
if ( !$persark ) $name="";
$title = @$_REQUEST['title'];
$from = @$_REQUEST['from'];
$to = @$_REQUEST['to'];
if ( $from ) $from = 0 + $from;
if ( $to ) $to = 0 + $to;
if ( !$from ) $from = $to;
if ( !$to ) $to = $from;

if ( $from > $to ) { $tmp = $from; $from = $to; $to = $tmp; }


$sql = "SELECT document.* FROM document";
if ( $title ) $sql.=", title";
if ( $persark ) $sql.=", contribution";
$sql.=" WHERE 1 ";
if ( $title ) $sql.=" AND title.text MATCH :title AND title.docid = document.id";
if ( $from && $to ) $sql.=" AND :from <= date AND date <= :to ";
if ( $persark ) $sql.=" AND contribution.person = :person AND contribution.document = document.id ";
if ( $from || $persark ) $sql.=" ORDER BY document.date";
$sql.=" LIMIT :limit ";

?><!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <link rel="stylesheet" type="text/css" href="cataviz.css"/>
    <style>
form label { display: block; }
form, header, footer, nav { user-select: none; -moz-user-select: none; -khtml-user-select: none; -webkit-user-select: none; -o-user-select: none; }
    </style>
  </head>
  <body>
    <?php include ( dirname(__FILE__).'/menu.php' ) ?>
    <header>
      <h1><a href="?">Liste bibliographique</a></h1>
      <form name="search">
        <label><span>Auteur</span>
            <input id="persark" name="persark" type="hidden" value="<?php echo $persark ?>"/>
            <input id="name" name="name" placeholder="Auteur ?" value="<?php echo $db->perstitle( $persark ) ?>" size="30"/>
            <select id="perslist" size="8">

            </select>
          </div>
        </label>
        <label><span>Titre</span>
          <input name="title" placeholder="Mots du titre ?" value="<?php echo $title ?>" size="30"/>
        </label>
        <label><span>Publié</span>
          entre <input name="from" placeholder="AAAA" size="4" value="<?php echo $from ?>"/> et <input name="to" placeholder="AAAA"  value="<?php echo $to ?>" size="4"/>
          <button name="go" type="submit">Chercher</button>
        </label>
      </form>
    </header>
      <?php
if ( $from || $title || $persark ) {
  $byline = $db->prepare( "SELECT * FROM person, contribution WHERE contribution.person = person.id AND contribution.document = ? " );
  echo '
  <table class="sortable">
    <tr>
      <th>N°</th>
      <th>Titre</th>
      <th>Auteur(s)</th>
      <th>Date</th>
      <th>Éditeur</th>
      <th>Lieu</th>
      <th>Notice</th>
      <th>Description</th>
    </tr>
';
  $query = $db->prepare($sql);
  if ( $title ) $query->bindValue( ':title', $title, PDO::PARAM_STR);
  if ( $from && $to ) {
    $query->bindValue(':from', $from, PDO::PARAM_INT);
    $query->bindValue(':to', $to, PDO::PARAM_INT);
  }
  if ( $persark ) {
    $person = Cataviz::ark2id( $persark );
    $query->bindValue(':person', $person, PDO::PARAM_INT);
  }
  $query->bindValue(':limit', $limit, PDO::PARAM_INT);
  $query->execute();
  $i = 1;
  while ( $doc = $query->fetch( PDO::FETCH_ASSOC ) ) {
    echo '<tr>';
    echo '<td>'.$i.'</td>';
    echo '<td>'.$doc['title'].'</td>';
    echo '<td>';
    $byline->execute( array( $doc['id']) );
    $j = 1;
    $first = true;
    while ( $author = $byline->fetch( PDO::FETCH_ASSOC ) ) {
      if ( $first ) $first = false;
      else echo " ; ";
      echo $author['family'];
      if ( $author['given'] ) echo ", ".$author['given'];
      if ( $j > 4 ) {
        echo "…";
        break;
      }
      $j++;
    }
    echo '</td>';
    echo '<td>'.$doc['date'].'</td>';
    echo '<td>'.$doc['publisher'].'</td>';
    echo '<td>'.$doc['place'].'</td>';
    $url = "http://catalogue.bnf.fr/ark:/12148/".$doc['ark'];
    echo '<td><a href="'.$url.'">'.$url.'</a></td>';
    echo '<td>'.$doc['description'].'</td>';
    echo "</tr>\n";
    $i++;
  }
  if ( $i > $limit ) {
    echo '<tr><th colspan="8">Liste limitée à 2000 enregistrements</th></tr>';
  }
}
      ?>
    </table>
    <script type="text/javascript" src="forms.js">//</script>
    <?php include ( dirname(__FILE__).'/footer.php' ) ?>
  </body>
</html>
