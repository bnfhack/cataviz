<?php
require_once(__DIR__ . "/../Cataviz.php");
use Oeuvres\Kit\{Http};

header("Access-Control-Allow-Origin:*");
header("Content-Type: application/json");


$q = Http::par('q');
if (!$q) return;
$deform = Cataviz::deform($q);

$time = microtime(true);

$sql ="SELECT * FROM pers WHERE deform >= ? AND deform <= ? ORDER BY docs DESC LIMIT 20"; //

$pers_q = Cataviz::prepare($sql);
$pers_q->execute([$deform, $deform.'~']);

print '{  "data": ['."\n";
$first = true;
while ( $pers_row = $pers_q->fetch( PDO::FETCH_ASSOC ) ) {
  if ($first) $first = false;
  else print ",\n";
  $label = Cataviz::pers_label($pers_row);
  print '  {"id":"' . $pers_row['id'] . '", "label":"'.$label.'", "docs":' . $pers_row['docs'].'}';
}
print '
], "meta": {"time": "' . number_format(microtime(true) - $time, 3) . 'ms."}}';
