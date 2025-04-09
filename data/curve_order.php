<?php
require_once(__DIR__ . "/../Cataviz.php");
use Oeuvres\Kit\{Http};


$start_time = microtime(true);
header("Access-Control-Allow-Origin:*");
header("Content-Type: application/json");

$start = Http::int('start', 1685, 1452, Cataviz::$p['date_max']);
$end = Http::int('end', 1913, 1452, Cataviz::$p['date_max']);

$sql = "SELECT count(*) AS count FROM doc WHERE year = ? ";

$queries = array(
    "Tout" => Cataviz::prepare($sql),
    "% 1er livre" => Cataviz::prepare($sql." AND order1 = 1"),
    // "2nd" => Cataviz::prepare($sql." AND order1 = 2"),
    "% 2nd-9e livre" => Cataviz::prepare($sql." AND order1 >= 2 AND order1 <= 9"),
    "% 10e et + livre" => Cataviz::prepare($sql." AND order1 >= 10"),
);


echo "{\n";
echo '    "data":[';
$first = true;
for ($year = $start; $year <= $end; $year++) {
    if ($first) $first = false;
    else echo ","; 
    echo "\n        [" . $year;
    foreach ($queries as $label => $q) {
        $q->execute(array($year));
        list($val) = $q->fetch(PDO::FETCH_NUM);
        if ($label == 'Tout') {
            $tout = $val;
            if ($tout == 0) $tout = 1;
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
            "plotter" => "Dygraph.plotHistory",
        ];
    
    }
}


$attrs = [
    "title" => "BnF, catalogue général, part des premiers livres",
    "ylabel" => "Nombre de titres par an",
    "y2label" => "% des titres d’une année",
    "rollPeriod" => 0,
    'strokeWidth' => ($start < 1750)?2:1,
    "fillAlpha" => 0.7,
    'drawPoints' => true,
    'pointSize' => 5,
    'historySmooth' => ($start < 1750)?1:0,
    'logscale' => false,
    "series" => $series,
];

echo ',
        "attrs": ' . json_encode($attrs);
echo ', 
        "time": "'. (microtime(true) - $start_time) * 1000 . 'ms."';
echo "\n    }\n";
echo "}\n";
