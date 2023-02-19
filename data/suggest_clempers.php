<?php
require_once(__DIR__ . "/../Cataviz.php");

use Oeuvres\Kit\{Http};

header("Access-Control-Allow-Origin:*");
// header("Content-Type: application/json");
// header("Content-Type: text/plain");

$clement = include(__DIR__ . "/clement.php");
$time_start = microtime(true);


$t = Http::par('t');
$start = Http::int('start', 1715, 1452, 1995);
$end = Http::int('end', 1788, 1452, 1995);

$limit = 100;
$sql = "SELECT COUNT(*) AS count, auth1 FROM doc WHERE clement = ? AND year >= ? AND year <= ? GROUP BY auth1 ORDER BY count DESC LIMIT ?";
$stmt = Cataviz::prepare($sql);
$stmt_pars = [];
$stmt_pars[] = $t;
$stmt_pars[] = $start;
$stmt_pars[] = $end;
$stmt_pars[] = $limit;
$stmt->execute($stmt_pars);

$stmt2 = Cataviz::prepare("SELECT * FROM auth WHERE id = ?");

print '{  "data": ['."\n";
$first = true;
$n = 0;
while ( $doc_row = $stmt->fetch(PDO::FETCH_ASSOC) ) {
    if ($first) $first = false;
    // else print ",\n";
    $count = $doc_row['count'];
    $line = [];
    $line['count'] = $count;
    $auth_id = $doc_row['auth1'];
    if (!$auth_id) {
        $line['label'] = '[anonyme]';
        $line['value'] = null;
    }
    else {
        $line['n'] = ++$n;
        $stmt2->execute([$auth_id]);
        $auth_row = $stmt2->fetch(PDO::FETCH_ASSOC);
        $line['value'] = intval($auth_row['id']);
        $label = Cataviz::auth_label($auth_row);
        $line['label'] = $label;
        $line['url'] = $auth_row['url'];
        echo "<div><a href=\"" . $line['url'] . "\"><small>$n.</small> $label, $count t.</a></div>\n";
    }
    // echo json_encode($line, JSON_UNESCAPED_UNICODE);
}
$meta = [
    "time" => number_format(microtime(true) - $time_start, 3) . 'ms',
    "start" => $start,
    "end" => $end,
    "t" => $t,
    "label" => isset($clement[$t])?$clement[$t]:$t,
];
print '
], "meta":' . json_encode($meta, JSON_UNESCAPED_UNICODE) . '}';

