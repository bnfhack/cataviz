<?php
require_once(__DIR__ . "/../Cataviz.php");
use Oeuvres\Kit\{Http};

header("Access-Control-Allow-Origin:*");
header("Content-Type: application/json");

$start = Http::int('start', 1685, 1452, 2020);
$end = Http::int('end', 1913, 1452, 2020);

$min = 200;
$sql = "SELECT count(*) AS c FROM doc WHERE year = ? ";

$queries = array(
    "Tout" => Cataviz::prepare($sql),
    "in-2°" => Cataviz::prepare($sql." AND format = 2"),
    "in-4°" => Cataviz::prepare($sql." AND format = 4"),
    "in-8°" => Cataviz::prepare($sql." AND format = 8"),
    "in-12°" => Cataviz::prepare($sql." AND format = 12"),
    "in-16+°" => Cataviz::prepare($sql." AND format >= 16"),
    "In-?°" => Cataviz::prepare($sql . " AND  format IS NULL"),
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
        if (!$val) $val = 'null';
        /*
        if ($val >= $min) echo ", " . $val;
        else echo ", null";
        */
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
            "series": {
                "in-2°": {
                    "plotter": "Dygraph.plotHistory"
                },
                "in-4°": {
                    "plotter": "Dygraph.plotHistory"
                },
                "in-8°": {
                    "plotter": "Dygraph.plotHistory"
                },
                "in-12°": {
                    "plotter": "Dygraph.plotHistory"
                },
                "in-16+°": {
                    "plotter": "Dygraph.plotHistory"
                },
                "In-?°": {
                    "pointSize": 0,
                    "color": "#ccc",
                    "strokeWidth": 1
                }
            }
        }';


echo "\n    }\n";
echo "}\n";
