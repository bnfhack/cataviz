<?php
require_once(__DIR__ . "/../Cataviz.php");
$clement = include(__DIR__ . "/clement.php");

use Oeuvres\Kit\{Http};

$start_time = microtime(true);
header("Access-Control-Allow-Origin:*");
header("Content-Type: application/json");

$start = Http::int('start', 1715, 1452, 2020);
$end = Http::int('end', 1788, 1452, 2020);


$sql = "SELECT count(*) AS count FROM doc WHERE year = ? AND clement = ? ";
$q = Cataviz::prepare($sql);
$sql = "SELECT count(*) AS count FROM doc WHERE year = ? AND clement IS NULL";
$q_null = Cataviz::prepare($sql);

// the grouping values
$terms = Http::pars('t');
// value if no point
$none = 1; // log

echo "{\n";
echo '    "data":[';
$first = true;
for ($year = $start; $year <= $end; $year++) {
    if ($first) $first = false;
    else echo ","; 
    echo "\n        ";
    $line = [];
    $line[] = $year;
    foreach ($terms as $t) {
        if (!$t || $t == '?') {
            $q_null->execute([$year]);
            list($count) = $q_null->fetch(PDO::FETCH_NUM);
        }
        else {
            $q->execute([$year, $t]);
            list($count) = $q->fetch(PDO::FETCH_NUM);
        }
        if (!$count) $count = $none;
        $line[] = intval($count);
    }
    echo json_encode($line, JSON_UNESCAPED_UNICODE);
    flush();
}
echo "\n    ],\n";
echo '    "meta":{'."\n";

echo '        "labels": ["Année"';
foreach ($terms as $t) {
    echo ', "';
    if (!$t) echo '?';
    else if (isset($clement[$t])) echo $clement[$t];
    else echo $t;
    echo '"';
}
echo "]";

echo ', 
        "time": "'. (microtime(true) - $start_time) * 1000 . 'ms."';
echo "\n    }\n";
echo "}\n";
