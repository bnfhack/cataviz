<?php
$time_start = microtime(true);
require_once(__DIR__ . "/../Cataviz.php");
use Oeuvres\Kit\{Http};

header("Access-Control-Allow-Origin:*");
header("Content-Type: application/json");

$limit = 50;
$q = Http::par('q');
$deform = Cataviz::deform($q);
if (!$q) {
    $sql ="SELECT * FROM pers ORDER BY docs DESC LIMIT $limit"; //
    $pers_q = Cataviz::prepare($sql);
    $pers_q->execute([]);
} else {
    $sql ="SELECT * FROM pers WHERE deform >= ? AND deform <= ? ORDER BY docs DESC LIMIT $limit"; //
    $pers_q = Cataviz::prepare($sql);
    $pers_q->execute([$deform, $deform.'~']);
}






print '{  "data": ['."\n";
$first = true;
while ( $pers_row = $pers_q->fetch( PDO::FETCH_ASSOC ) ) {
    if ($first) $first = false;
    else print ",\n";
    $line = [];
    $line['value'] = intval($pers_row['id']);
    $line['label'] = Cataviz::pers_label($pers_row);
    $line['count'] = $pers_row['docs'];
    echo json_encode($line, JSON_UNESCAPED_UNICODE);
}
print '
], "meta": {"time": "' . number_format(microtime(true) - $time_start, 3) . 'ms."}}';
