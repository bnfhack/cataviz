<?php
header('Content-Type: application/javascript; charset=utf-8');
if ( isset( $_REQUEST['q'] ) ) $q = $_REQUEST['q'];
$callback = "perspop";
if ( isset( $_REQUEST['callback'] ) ) $callback = $_REQUEST['callback'];
$limit = 20;
if ( isset( $_REQUEST['limit'] ) ) $limit = $_REQUEST['limit'];
echo $callback."( [ \n";
if ( isset( $q ) ) {
  include ( dirname(__FILE__).'/Cataviz.php' );
  include( dirname( __FILE__ )."/lib/frtr.php" ); // crée une variable $frtr
  $db = new Cataviz( "databnf.sqlite" );
  $q = strtr( $q, $frtr );
  $qpers = $db->prepare("SELECT * FROM person WHERE sort >= ? AND sort <= ? ORDER BY posthum DESC LIMIT ?"); //
  $qpers->execute( array( $q, $q.'~', $limit) );
  $first = true;
  while( $pers = $qpers->fetch( PDO::FETCH_ASSOC ) ) {
    if ( $first ) $first = false;
    else echo ",\n";
    echo '  [ ';
    echo $pers['id'];
    echo ', "'.$pers['ark'].'"';
    echo ', "'.$pers['family'];
    if ( $pers['given'] ) echo ", ".$pers['given'];
    if ( $pers['birthyear'] || $pers['deathyear'] ) echo " (".$pers['birthyear'].'–'.$pers['deathyear'].")";
    echo '"';
    echo ' ]';
  }
}
echo "\n] );\n";

?>
