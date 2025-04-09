<?php
require_once(__DIR__ . "/../Cataviz.php");
$clement = include(__DIR__ . "/clement.php");

use Oeuvres\Kit\{Http};

header("Access-Control-Allow-Origin:*");
header("Content-Type: application/json");

$start_time = microtime(true);

$where = [];
$start = Http::int('start', null, 1452, Cataviz::$p['date_max']);
if ($start) $where[] = "year >= $start";
$end = Http::int('end', null, 1452, Cataviz::$p['date_max']);
if ($end < $start) $end = null;
if ($end > 2015) $end = null;
if ($end) $where[] = "year <= $end";
$where = implode(' AND ', $where);
if ($where) $where = " WHERE " . $where;

$sql = "SELECT clement, COUNT(*) AS count FROM doc $where GROUP BY clement ORDER BY count DESC LIMIT 50";


$q = Cataviz::prepare($sql);
$q->execute();

$n_max = 50;
$n = 0;
print '{  "data": ['."\n";
$first = true;
while ( $row = $q->fetch( PDO::FETCH_ASSOC ) ) {
    if ($first) $first = false;
    else print ",\n";

    $line = [];
    $line['n'] = ++$n;
    $line['value'] = $row['clement'];
    if (isset($clement[$row['clement']])) {
        $line['label'] = $clement[$row['clement']];
    }
    else {
        $line['label'] = $row['clement'];
    }
    $line['count'] = intval($row['count']);
    echo json_encode($line, JSON_UNESCAPED_UNICODE);
    flush();
}
print '
], "meta": {"time": "' . number_format(microtime(true) - $start_time, 3) . 'ms."}}';
    