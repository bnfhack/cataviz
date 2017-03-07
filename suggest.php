<?php
header('Content-type: application/javascript; charset=utf-8');
include ( dirname(__FILE__).'/frtr.php' );
include ( dirname(__FILE__).'/Cataviz.php' );
$db = new Cataviz( "databnf.sqlite" );
$q = @$_REQUEST['q'];
if (!$q) return;
$sort = strtr( $q, $frtr );
$time = microtime(true);
$qpers = $db->prepare("SELECT code, family, given, docs FROM person WHERE sort >= ? AND sort <= ? ORDER BY docs DESC LIMIT 20"); //
$qpers->execute( array($sort, $sort.'~') );
echo "[\n";
while ( $pers = $qpers->fetch( PDO::FETCH_ASSOC ) ) {
  $label = $pers['family'];
  if ($pers['given']) $label .= ', '.$pers['given'];
  echo '  ["'.$label.' ('.$pers['docs'].')","'.$pers['code'].'"],'."\n";
}
echo "]\n";
echo "// ".number_format(microtime(true) - $time, 3)."s. ";
?>
