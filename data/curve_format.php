<?php
require_once(__DIR__ . "/../Cataviz.php");
use Oeuvres\Kit\{Http};

header("Access-Control-Allow-Origin:*");
header("Content-Type: application/json");

$start = Http::int('start', 1685, 1452, Cataviz::$p['date_max']);
$end = Http::int('end', 1913, 1452, Cataviz::$p['date_max']);

$min = 200;
$sql = "SELECT count(*) AS c FROM doc WHERE year = ? ";

$queries = array(
    "Tout" => Cataviz::prepare($sql),
    "% in-8°" => Cataviz::prepare($sql." AND format = 8"),
    "% in-4°" => Cataviz::prepare($sql." AND format = 4"),
    "% in-16+°" => Cataviz::prepare($sql." AND format >= 16"),
    "% in-2°" => Cataviz::prepare($sql." AND format = 2"),
    "% in-12°" => Cataviz::prepare($sql." AND format = 12"),
    "% In-?°" => Cataviz::prepare($sql . " AND  format IS NULL"),
);


echo "{\n";
echo '    "data":[';
$first = true;
for ($year = $start; $year <= $end; $year++) {
    if ($first) $first = false;
    else echo ","; 
    echo "\n        [" . $year;
    $tout = null;
    foreach ($queries as $label => $q) {
        $q->execute(array($year));
        list($val) = $q->fetch(PDO::FETCH_NUM);
        if (!$val) {
            $val = 'null';
        }
        else if ($label == 'Tout') {
            $tout = $val;
        }
        else {
            $val = round(10000.0 * $val/  $tout) / 100.0;
        }
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
$series = [];
foreach ($queries as $label => $q) {
    if ($label == 'Tout') {
        $series[$label] = [
            "axis" => "y1",
            "fillAlpha" => 0.2,
            'fillGraph' => true,
            'drawPoints' => false,
            'strokeWidth' => 0,
        ];
    }
    else {
        $series[$label] = [
            "axis" => "y2",
            "pointSize" => 5,
            'strokeWidth' => 2, // ($start < 1750)?2:1,
            "plotter" => "Dygraph.plotHistory",
        ];
    
    }
}


$attrs = [
    "title" => "BnF, catalogue général, répartition des formats",
    "ylabel" => "Nombre de titres par an",
    "y2label" => "% des titres d’une année",
    "fillAlpha" => 0.7,
    'historySmooth' => ($start < 1750)?1:0,
    // 'logscale' => true,
    "series" => $series,
];

echo ',
        "attrs": ' . json_encode($attrs);



echo "\n    }\n";
echo "}\n";
