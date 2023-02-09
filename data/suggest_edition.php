<?php
require_once(__DIR__ . "/../Cataviz.php");
use Oeuvres\Kit\{Http};

$time = microtime(true);

header("Access-Control-Allow-Origin:*");
header("Content-Type: application/json");

$f = Http::par('f', 'place');
if ($f == 'publisher') {
  $table = 'doc';
  $col_group = 'publisher_group';
  $col_like = 'publisher_like';
}
else {
  $table = 'doc';
  $col_group = 'place_group';
  $col_like = 'place_like';
}

$where = [];
$pars = [];

$q = Http::par('q');
if ($q) {
  $where[] = "($col_like >= ? AND $col_like <= ?)";
  $deform = Cataviz::deform($q);
  // [$deform, $deform.'~']
  $pars[] = $deform;
  $pars[] = $deform.'~';
}
// default period for modERN 1715-1788
$start = Http::int('start', 1715, 1452, 2020);
if ($start) $where[] = "year >= $start";
$end = Http::int('end', 1788, 1452, 2020);
if ($end < $start) $end = null;
if ($end > 2015) $end = null;
if ($end) $where[] = "year <= $end";
$where = implode(' AND ', $where);
if ($where) $where = " WHERE " . $where;


// years
$sql ="SELECT $col_group as value, count(*) AS count FROM $table $where GROUP BY $col_group ORDER BY count DESC LIMIT 30"; //

$q = Cataviz::prepare($sql);
$q->execute($pars);

print '{  "data": ['."\n";
$first = true;
$n = 0;
while ( $row = $q->fetch( PDO::FETCH_ASSOC ) ) {
    if ($first) $first = false;
    else print ",\n";
    if (!$row['value']) $row['value'] = '?';
    $line = [];
    $line['n'] = ++$n;
    $line['value'] = $row['value'];
    $line['label'] = $row['value'];
    $line['count'] = intval($row['count']);
    echo json_encode($line, JSON_UNESCAPED_UNICODE);
    flush();

}
print '
], "meta": {"time": "' . number_format(microtime(true) - $time, 3) . 'ms.", "start":"' . $start . '", "end":"' . $end . '", "sql":"' . $sql . '"}}';
