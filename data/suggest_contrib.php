<?php
if (!isset($table)) $table = "contrib";
$time_start = microtime(true);
require_once(__DIR__ . "/../Cataviz.php");
use Oeuvres\Kit\{Http};

header("Access-Control-Allow-Origin:*");
header("Content-Type: application/json");


$limit = 50;

$stmt_pars = [];
$stmt_pars[] = Http::int('start', 1452, 1452, 2019);
$stmt_pars[] = Http::int('end', 2019, 1452, 2019);

$pers_sql = '';
$pers_where = [];
$after = Http::int('after');
if ($after) {
    $pers_where[] = "generation >= ?";
    $stmt_pars[] = $after;
}
$before = Http::int('before');
if ($before) {
    $pers_where[] = "generation <= ?";
    $stmt_pars[] = $before;
}
$q = Http::par('q');
if ($q) {
    $deform = Cataviz::deform($q);
    $pers_where[] = "deform >= ?";
    $stmt_pars[] = $deform;
    $pers_where[] = "deform <= ?";
    $stmt_pars[] = $deform.'~';
}
if (count($pers_where)) {
    $pers_sql = "AND pers in (SELECT id FROM pers WHERE " . implode (' AND ', $pers_where) .")";
}

$stmt_pars[] = 100;


$stmt2 = Cataviz::prepare("SELECT * FROM pers WHERE id = ?");
$type = ($table == 'contrib')?' AND type = 1':''; 
$sql = "
SELECT pers, count(*) AS count 
FROM $table
WHERE 
    year >= ? AND year <= ? 
    $type
    $pers_sql
    GROUP BY pers 
    ORDER BY count DESC
    LIMIT ?;
";
$stmt = Cataviz::prepare($sql);
$stmt->execute($stmt_pars);

print '{  "data": ['."\n";
$first = true;
$n = 0;
while ( $row = $stmt->fetch(PDO::FETCH_ASSOC) ) {
    $pers_id = $row['pers'];
    $count = $row['count'];
    $stmt2->execute([$pers_id]);
    $row = $stmt2->fetch(PDO::FETCH_ASSOC);
    if ($first) $first = false;
    else print ",\n";
    $line = [];
    $line['n'] = ++$n;
    $line['value'] = intval($row['id']);
    $line['label'] = Cataviz::pers_label($row);
    $line['count'] = $count;
    echo json_encode($line, JSON_UNESCAPED_UNICODE);
}

print '
], "meta": {"time": "' . number_format(microtime(true) - $time_start, 3) . 'ms."}}';

