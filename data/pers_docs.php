<?php
require_once(__DIR__ . "/../Cataviz.php");

$start_time = microtime(true);
header("Access-Control-Allow-Origin:*");
header("Content-Type: application/json");

$sql = "SELECT count(*) AS count FROM pers WHERE doc1 = ? ";

$tout = "Tout";
$femmes = "Femmes";
$hommes = "Hommes";
$inconnu = "?";
$doc1ratio = "Part de premiers livres";

$queries = array(
    $tout => Cataviz::prepare($sql),
    $femmes  => Cataviz::prepare($sql." AND gender = 2"),
    $hommes => Cataviz::prepare($sql." AND gender = 1"),
    $inconnu => Cataviz::prepare($sql." AND gender IS NULL"),
    $doc1ratio => Cataviz::prepare("SELECT count(*) AS count FROM doc WHERE year = ?"),
);


echo "{\n";
echo '    "data":[';
$first = true;
$row = [];
for ($year = Cataviz::$p['from']; $year <= Cataviz::$p['to']; $year++) {
    if ($first) $first = false;
    else echo ","; 
    echo "\n        [" . $year;
    foreach ($queries as $label => $q) {
        $q->execute(array($year));
        list($val) = $q->fetch(PDO::FETCH_NUM);
        if (!$val) $val = 'null';
        else if ($label == $doc1ratio) {
            $val = round(10000.0 * $row[$tout] / $val) / 100.0;
        }

        $row[$label] = $val;
        echo ", " . $val;
    }
    echo "]";
}
echo "\n    ],\n";
echo '    "meta":{'."\n";

//    labels: [ "Année", "Titres", "PNB/h"],
echo '        "labels": ["Année"';
foreach ($queries as $label => $q) {
    echo ', "' . $label . '"';
}
echo "]";
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
echo ', 
        "time": "'. (microtime(true) - $start_time) * 1000 . 'ms."';
echo "\n    }\n";
echo "}\n";
