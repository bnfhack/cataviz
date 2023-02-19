<?php
require_once(__DIR__ . "/../Cataviz.php");
use Oeuvres\Kit\{Http};

$start_time = microtime(true);
header("Access-Control-Allow-Origin:*");
header("Content-Type: application/json");
// header("Content-Type: text/plain");

$none = 0;

$start = Http::int('start', 1685, 1452, 2019);
$end = Http::int('end', 1913, 1452, 2019);


$sql = "SELECT count(*) AS count FROM auth WHERE doc1 = ? ";

$tout = "Tout";
$femmes = "Femmes";
$hommes = "Hommes";
$femrate = "% femmes";
$inconnu = "?";
$zero = '0';

$queries = array(
    $femmes  => Cataviz::prepare( "SELECT count(*) AS count FROM doc WHERE year = ? AND gender1 = 2"),
    $inconnu => Cataviz::prepare( "SELECT -count(*) AS count FROM doc WHERE year = ? AND  gender1 IS NULL AND type1 = 1"),
    $hommes => Cataviz::prepare( "SELECT -count(*) AS count FROM doc WHERE year = ? AND type1 = 1 AND (gender1 = 1 OR gender1 IS NULL)"),
    $femrate => Cataviz::prepare("SELECT count(*) AS count FROM doc WHERE year = ? AND type1 = 1"),
);


echo "{\n";
echo '    "data":[';
$first = true;
for ($year = $start; $year <= $end; $year++) {
    if ($first) $first = false;
    else echo ","; 
    echo "\n        [" . $year;
    $row = [];
    foreach ($queries as $label => $q) {
        $val = 0;
        if ($q) {
            $q->execute(array($year));
            list($val) = $q->fetch(PDO::FETCH_NUM);
        }
        if ($label === $femrate) {
            $val = round(10000.0 *  $row[$femmes] / $val) / 100.0;
        }
        else if ($label === $zero) {
            $val = -1;
        }

        if (!$val) $val = $none;
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
$attrs = [
    "ylabel" => "Auteurs à la date de leur premier titre",
    "y2label" => "% de femmes",
    "rollPeriod" => 0,
    "strokeWidth" => 0,
    "fillAlpha" => 0.7,
    "pointSize" => 0,
    'historySmooth' => 1,
    "series" => [
        $femmes => [
            "color" => 'hsla(45, 80%, 50%, 1)',
            'fillGraph' => true,
        ],
        $hommes => [
            "color" => 'hsla(100, 30%, 50%, 1)',
            'fillGraph' => true,
        ],
        $inconnu => [
            "color" => '#ccc',
            "fillAlpha" => 0.2,
            'fillGraph' => true,
        ],
        $femrate => [
            "axis" => "y2",
            "color" => "#fff",
            "pointSize" => 3,
            "strokeWidth" => 1,
            "fillGraph" => false,
            "plotter" => "Dygraph.plotHistory",
        ]
    ]
];
echo ',
        "attrs": ' . json_encode($attrs);
echo ', 
        "time": "'. (microtime(true) - $start_time) * 1000 . 'ms."';
echo "\n    }\n";
echo "}\n";
