<?php
include ( dirname(__FILE__).'/Cataviz.php' );
$db = new Cataviz( "databnf.db" );

/*
Boucler sur des périodes temporelles pour chercher les auteurs les plus appelés
*/
$sql = "SELECT person, count(*) AS count FROM contribution WHERE writes=1 AND dead=1 AND date >= ? AND date <= ? GROUP BY person ORDER BY count DESC LIMIT 20";
// Roles ?
$qtop = $db->prepare($sql);
$qpers = $db->prepare("SELECT name, note FROM person WHERE id = ?");


$from = 1960;
$to = 2015;
$step = 5;
?><!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <link rel="stylesheet" type="text/css" href="cataviz.css"/>
  </head>
  <body>
    <table class="data">
      <tr>
<?php
for ( $year = $from; $year <= $to; $year += $step ) {
  echo '<th>'.$year."</th>\n";
}
?>
      </tr>
      <tr>
<?php
$generation = 24;
for ( $year = $from; $year <= $to; $year += $step ) {
  echo '<td valign="top" nowrap="nowrap">'."\n";
  $qtop->execute( array( $year - $generation, $year) );
  while ( $row=$qtop->fetch( PDO::FETCH_ASSOC ) ) {
    $qpers->execute( array( $row['person'] ) );
    $pers = $qpers->fetch( PDO::FETCH_ASSOC );
    echo '<div title="'.$pers['note'].'">'.$pers['name']." (".$row['count'].")</div>\n";
  }
  echo "</td>\n";
}
?>
      <tr>
    </table>
  </body>
</html>
