<?php
include ( dirname(__FILE__).'/Cataviz.php' );
$db = new Cataviz( "databnf.sqlite" );
$from = @$_REQUEST['from'];
$to = @$_REQUEST['to'];
$pre = @$_REQUEST['pre'];
$pstart = @$_REQUEST['pstart'];
$pend = @$_REQUEST['pend'];
$dead = @$_REQUEST['dead'];
$orderpers = 'posthum';
if ( isset($_REQUEST['orderpers']) ) $orderpers = $_REQUEST['orderpers'];
if ( !preg_match( '/posthum|anthum|docs/', $orderpers ) ) $orderpers = 'posthum';
if ( isset($_REQUEST['limit']) ) $limit = 0+$_REQUEST['limit'];
if ( !isset($limit) || $limit < 1 || $limit > 500 ) $limit = 50;

?><!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <script src="lib/dygraph.min.js">//</script>
    <link rel="stylesheet" type="text/css" href="lib/dygraph.css"/>
    <link rel="stylesheet" type="text/css" href="cataviz.css"/>
  </head>
  <body>
    <?php include ( dirname(__FILE__).'/menu.php' ) ?>
    <h1><a href="?">Palmarès</a></h1>
    <form name="person">
      Chercher un auteur commençant par <input name="pre" value="<?php echo $pre ?>"/>
      <br/>ou né entre <input name="from" size="4" value="<?php echo $from ?>"/> et <input name="to" size="4" value="<?php echo $to ?>"/>
      <br/>ou publié entre <input name="pstart" size="4" value="<?php echo $pstart ?>"/> et <input name="pend" size="4" value="<?php echo $pend ?>"/>
      <br/><br/>
      <input name="limit" size="4" value="<?php echo $limit ?>"/> noms
      rangé par nombre de documents
      <br/><label title="Publiés après la mort" class="radio"><input name="orderpers" value="posthum" type="radio" <?php if ($orderpers == "posthum") echo ' checked="checked"'; ?>/> posthumes</label>,
      <label title="Publiés avant la mort" class="radio"><input name="orderpers" value="anthum" type="radio" <?php if ($orderpers == "anthum") echo ' checked="checked"'; ?>/> anthumes</label>,
      <label class="radio"><input name="orderpers" value="docs" type="radio" <?php if ($orderpers == "docs") echo ' checked="checked"'; ?>/> tous</label>
      <br/><input type="submit"/>
    </form>
    <table class="sortable">
      <tr>
        <th>N°</th>
        <th>Auteur</th>
        <th>Naissance</th>
        <th>Mort</th>
        <th>Documents</th>
        <th>Du vivant</th>
        <th>Après la mort</th>
        <th>Non datés</th>
      </tr>
      <?php
if ( $pre ) {
  include( dirname( __FILE__ )."/frtr.php" ); // crée une variable $frtr
  $pre = strtr( $pre, $frtr );
  $qpers = $db->prepare("SELECT * FROM person WHERE sort >= ? AND sort <= ? ORDER BY posthum DESC LIMIT ?"); //
  $qpers->execute( array( $pre, $pre.'~', $limit) );
}
else if ( $pstart && $pend ) {
  $sql = "SELECT person, count(*) AS score
    FROM contribution
    WHERE writes=1
    AND date >= ?
    AND date <= ?"
  ;
  if ( $orderpers == 'posthum' )  $sql .= " AND posthum=1";
  else if ( $orderpers == 'anthum' ) $sql .= " AND posthum IS NULL ";
  $sql .= " GROUP BY person ORDER BY score DESC LIMIT ?";
  $qpers = $db->prepare( $sql );
  $qpers->execute( array( $pstart, $pend, $limit ) );
}
else if ( is_numeric($from) && is_numeric($to) ) {
  $sql = "SELECT * FROM person WHERE birthyear >= ? AND birthyear <= ? ORDER BY ".$orderpers." DESC LIMIT ?";
  $qpers = $db->prepare( $sql );
  $qpers->execute( array( $from, $to, $limit ) );
}
else {
  $sql = "SELECT * FROM person ORDER BY ".$orderpers." DESC LIMIT ? ";
  $qpers = $db->prepare( $sql );
  $qpers->execute( array( $limit ) );
}
$i = 1;
// pour une requête plus efficace
$qnopers = $db->prepare( "SELECT * FROM person WHERE id = ?" );
while( $pers = $qpers->fetch( PDO::FETCH_ASSOC ) ) {
  // requête uniquement par les contributions
  if ( !isset( $pers['name'] ) && isset( $pers['person'] ) ) {
    $qnopers->execute( array($pers['person']) );
    $pers = $qnopers->fetch( PDO::FETCH_ASSOC );
  }
  $label = $pers['name'];
  if ($pers['family']) {
    $label = $pers['family'];
    if ($pers['given']) $label .= ', '.$pers['given'];
  }
  if ( isset( $pers['score'] ) ) $docs = $pers['score'];
  else $docs = $pers['docs'];
  echo '  <tr>';
  echo '    <td align="right">'.$i.'</td>';
  echo '    <td><a href="auteur.php?persark='.$pers['ark'].'">'.$label.'</a></td>';
  echo '    <td>'.$pers['birthyear'].'</td>';
  echo '    <td>'.$pers['deathyear'].'</td>';
  echo '    <td align="right">'.$docs.'</td>';
  echo '    <td align="right">'.$pers['anthum'].'</td>';
  echo '    <td align="right">'.$pers['posthum'].'</td>';
  // non datés
  echo '    <td align="right">'.( $pers['docs']-$pers['anthum']-$pers['posthum'] ).'</td>';
  echo '  </tr>';
  $i++;
}
      ?>
    </table>
    <script type="text/javascript" src="Sortable.js">//</script>
    <script type="text/javascript" src="Suggest.js">//</script>
    <?php include ( dirname(__FILE__).'/footer.php' ) ?>
  </body>
</html>
<?php

?>
