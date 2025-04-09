<?php
if (!isset($table)) $table = 'contrib';
if (!isset($cumul)) $cumul = false;


$start_time = microtime(true);

require_once(__DIR__ . "/../Cataviz.php");
use Oeuvres\Kit\{Http};

header("Access-Control-Allow-Origin:*");
header("Content-Type: application/json");

$start = Http::int('start', 1685, 1452, Cataviz::$p['date_max']);
$end = Http::int('end', 1913, 1452, Cataviz::$p['date_max']);



$auth_http = Http::pars('auth');
// filter non existent ids
$sql = "SELECT * FROM auth WHERE id = ?";
$auth_q = Cataviz::prepare($sql);
$auth_ids = [];
// check auth list
for ($i=0, $len=count($auth_http); $i < $len; $i++) {
    $id = $auth_http[$i];
    $auth_q->execute([$id]);
    $auth_row = $auth_q->fetch();
    if (!$auth_row) continue;
    $label = $auth_row['name'];
    if ($auth_row['birthyear'] || $auth_row['deathyear']) {
        $label .= " (";
        if ($auth_row['birthyear']) $label .= $auth_row['birthyear'];
        $label .= ' / ';
        if ($auth_row['deathyear']) $label .= $auth_row['deathyear'];
        $label .= ")";
    }
    $auth_ids[$id] = $label;
}


$sql = "SELECT count(*) AS count FROM $table WHERE auth = ? AND year = ?";
$auth_q = Cataviz::prepare($sql);

echo "{\n";
echo '    "data":[';
$first = true;
$sum = [];
foreach ($auth_ids as $id => $label) $sum[$id] = 0;
for ($year = $start; $year <= $end; $year++) {
    if ($first) $first = false;
    else echo ","; 
    echo "\n        [" . $year;
    foreach ($auth_ids as $id => $label) {
        $auth_q->execute([$id, $year]);
        list($count) = $auth_q->fetch(PDO::FETCH_NUM);
        $sum[$id] += $count;

        if ($cumul) $val = $sum[$id]; 
        else if (!$count) $val = 0;
        else $val = $count;

        echo ", " . $val;
    }
    echo "]";
}
echo "\n    ],\n";
echo '    "meta":{'."\n";

//    labels: [ "Année", "Titres", "PNB/h"],
echo '        "labels": ["Année"';
foreach ($auth_ids as $id => $label) {
    echo ', "' . $label . '"';
}
echo "]";
/*
// per series infos
echo ',
        "attrs": {
            "y2label":"%",
            "series": {
                "' . $inconnu . '": {
                    "pointSize": 0,
                    "color": "#ccc",
                    "strokeWidth": 1
                },
                "' . $hommes . '": {
                    "plotter": "Dygraph.plotHistory"
                },
                "' . $femmes . '": {
                    "plotter": "Dygraph.plotHistory"
                },
                "' . $doc1ratio . '": {
                    "axis": "y2",
                    "strokeWidth": 0.1,
                    "stackedGraph": true,
                    "fillGraph": true,
                    "color": "#aaf"
                }
            }
        }';
*/
echo ', 
        "time": "'. (microtime(true) - $start_time) * 1000 . 'ms."';
echo "\n    }\n";
echo "}\n";
