<?php
require_once(__DIR__ . "/../Cataviz.php");

use Oeuvres\Kit\{Http};

$start_time = microtime(true);
header("Access-Control-Allow-Origin:*");
header("Content-Type: application/json");

// have one ore more person nb, by ark ()

$pers_http = Http::pars('pers');
// filter non existent ids
$sql = "SELECT * FROM pers WHERE id = ?";
$pers_q = Cataviz::prepare($sql);
$pers_ids = [];
// check pers list
for ($i=0, $len=count($pers_http); $i < $len; $i++) {
    $id = $pers_http[$i];
    $pers_q->execute([$id]);
    $pers_row = $pers_q->fetch();
    if (!$pers_row) continue;
    $label = $pers_row['name'];
    if ($pers_row['birthyear'] || $pers_row['deathyear']) {
        $label .= " (";
        if ($pers_row['birthyear']) $label .= $pers_row['birthyear'];
        $label .= ' / ';
        if ($pers_row['deathyear']) $label .= $pers_row['deathyear'];
        $label .= ")";
    }
    $pers_ids[$id] = $label;
}

$sql = "SELECT count(*) AS count FROM contrib WHERE pers = ? AND year = ?";
$pers_q = Cataviz::prepare($sql);

echo "{\n";
echo '    "data":[';
$first = true;
$row = []; // maybe used to build a value from others
for ($year = Cataviz::$p['from']; $year <= Cataviz::$p['to']; $year++) {
    if ($first) $first = false;
    else echo ","; 
    echo "\n        [" . $year;
    foreach ($pers_ids as $id => $label) {
        $pers_q->execute([$id, $year]);
        list($val) = $pers_q->fetch(PDO::FETCH_NUM);
        if (!$val) $val = 0;
        $row[$label] = $val;
        echo ", " . $val;
    }
    echo "]";
}
echo "\n    ],\n";
echo '    "meta":{'."\n";

//    labels: [ "Année", "Titres", "PNB/h"],
echo '        "labels": ["Année"';
foreach ($pers_ids as $id => $label) {
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
