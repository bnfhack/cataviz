<?php
require_once(__DIR__ . "/../Cataviz.php");

use Oeuvres\Kit\{Http};

$start_time = microtime(true);
header("Access-Control-Allow-Origin:*");
header("Content-Type: application/json");

$start = Http::int('start', 1685, 1452, 2020);
$end = Http::int('end', 1913, 1452, 2020);


// get grouping values
$field = "place_group";
$terms = Http::pars('t');

$sql = "SELECT count(*) AS count FROM doc WHERE year = ? AND place_group = ? ";
$q = Cataviz::prepare($sql);
$sql = "SELECT count(*) AS count FROM doc WHERE year = ? AND place_group IS NULL";
$q_null = Cataviz::prepare($sql);

echo "{\n";
echo '    "data":[';
$first = true;
$row = []; // maybe used to build a value from others
for ($year = $start; $year <= $end; $year++) {
    if ($first) $first = false;
    else echo ","; 
    echo "\n        [" . $year;
    foreach ($terms as $t) {
        if (!$t || $t == '?') {
            $q_null->execute([$year]);
            list($count) = $q_null->fetch(PDO::FETCH_NUM);
        }
        else {
            $q->execute([$year, $t]);
            list($count) = $q->fetch(PDO::FETCH_NUM);
        }
        if (!$count) $count = 1;
        $row[$t] = $count;
        echo ", " . $count;
    }
    echo "]";
}
echo "\n    ],\n";
echo '    "meta":{'."\n";

echo '        "labels": ["Année"';
foreach ($terms as $t) {
    echo ', "' . $t . '"';
}
echo "]";

echo ', 
        "time": "'. (microtime(true) - $start_time) * 1000 . 'ms."';
echo "\n    }\n";
echo "}\n";
