<?php
require_once(__DIR__ . "/../Cataviz.php");
header("Access-Control-Allow-Origin:*");
header("Content-Type: application/json");

$sql = "SELECT count(*) AS count FROM doc WHERE year = ? ";

$queries = array(
    "Tout" => Cataviz::prepare($sql),
    // "2 p." => Cataviz::prepare($sql." AND pages <= 2"), // insignifiant
    "2-62p." => Cataviz::prepare($sql." AND pages < 64"),
    "64-192p." => Cataviz::prepare($sql." AND pages >= 64 AND pages <= 192"),
    "192+p." => Cataviz::prepare($sql." AND pages > 192"),
    // "]512, …] p." => Cataviz::prepare($sql."pages > 512"),
    "? p." => Cataviz::prepare($sql." AND pages IS NULL"),
    "moy. p." => Cataviz::prepare("SELECT AVG(pages) FROM doc WHERE year = ?")
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
            "y2label":"Moyenne nb de pages",
            "series": {
                "moy. p.": {
                    "axis": "y2",
                    "strokeWidth": 0.1,
                    "stackedGraph": true,
                    "fillGraph": true,
                    "color": "#aaf"
                },
                "? p.": {
                    "pointSize": 0,
                    "color": "#ccc",
                    "strokeWidth": 1
                },
                "2-62p.": {
                    "plotter": "Dygraph.plotHistory"
                },
                "64-192p.": {
                    "plotter": "Dygraph.plotHistory"
                },
                "192+p.": {
                    "plotter": "Dygraph.plotHistory"
                }
            }
        }';
echo "\n    }\n";
echo "}\n";
