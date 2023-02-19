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

$auth_sql = '';
$auth_where = [];
$after = Http::int('after');
if ($after) {
    $auth_where[] = "generation >= ?";
    $stmt_pars[] = $after;
}
$before = Http::int('before');
if ($before) {
    $auth_where[] = "generation <= ?";
    $stmt_pars[] = $before;
}
$q = Http::par('q');
if ($q) {
    $deform = Cataviz::deform($q);
    $auth_where[] = "deform >= ?";
    $stmt_pars[] = $deform;
    $auth_where[] = "deform <= ?";
    $stmt_pars[] = $deform.'~';
}
if (count($auth_where)) {
    $auth_sql = "AND auth in (SELECT id FROM auth WHERE " . implode (' AND ', $auth_where) .")";
}

$stmt_pars[] = 100;


$stmt2 = Cataviz::prepare("SELECT * FROM auth WHERE id = ?");
$type = ($table == 'contrib')?' AND type = 1':''; 
$sql = "
SELECT auth, count(*) AS count 
FROM $table
WHERE 
    year >= ? AND year <= ? 
    $type
    $auth_sql
    GROUP BY auth 
    ORDER BY count DESC
    LIMIT ?;
";
$stmt = Cataviz::prepare($sql);
$stmt->execute($stmt_pars);

print '{  "data": ['."\n";
$first = true;
$n = 0;
while ( $row = $stmt->fetch(PDO::FETCH_ASSOC) ) {
    $auth_id = $row['auth'];
    $count = $row['count'];
    $stmt2->execute([$auth_id]);
    $row = $stmt2->fetch(PDO::FETCH_ASSOC);
    if ($first) $first = false;
    else print ",\n";
    $line = [];
    $line['n'] = ++$n;
    $line['value'] = intval($row['id']);
    $line['label'] = Cataviz::auth_label($row);
    $line['count'] = $count;
    echo json_encode($line, JSON_UNESCAPED_UNICODE);
}

print '
], "meta": {"time": "' . number_format(microtime(true) - $time_start, 3) . 'ms."}}';

