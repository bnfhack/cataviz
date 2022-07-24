<?php
require_once(__DIR__ . "/../Cataviz.php");
header("Access-Control-Allow-Origin:*");
header("Content-Type: application/json");

$min = 200;
$sql = "SELECT count(*) AS c FROM doc WHERE year = ? AND ";

$queries = array(
    "In-?°" => Cataviz::prepare($sql."format IS NULL"),
    "in-2°" => Cataviz::prepare($sql."format = 2"),
    "in-4°" => Cataviz::prepare($sql."format = 4"),
    "in-8°" => Cataviz::prepare($sql."format = 8"),
    "in-12°" => Cataviz::prepare($sql."format = 12"),
    "in-16+°" => Cataviz::prepare($sql."format >= 16"),
    // "]512, …] p." => Cataviz::prepare($sql."pages > 512"),
);


echo "{\n";
echo '    "data":[';
$first = true;
for ($year = Cataviz::$p['from']; $year <= Cataviz::$p['to']; $year++) {
    if ($first) $first = false;
    else echo ","; 
    echo "\n        [" . $year;
    foreach ($queries as $label => $q) {
        $q->execute(array($year));
        list($val) = $q->fetch(PDO::FETCH_NUM);
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
// attrs.series:
echo ',
        "attrs": {
            "series": {
                "? p.": {
                    "pointSize": 0,
                    "color": "hsla(0, 0%, 0%, 1)",
                    "strokeWidth": 4
                }
            }
        }';

echo "\n    }\n";
echo "}\n";
