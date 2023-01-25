<?php
require_once(__DIR__ . "/../Cataviz.php");

header("Access-Control-Allow-Origin:*");
header("Content-Type: application/json");

$sql = "SELECT count(*) AS count FROM doc WHERE year = ? ";


$queries = array(
    "Tout" => Cataviz::prepare($sql),
    "Français" => Cataviz::prepare($sql . " AND lang = 'fre'"),
    "Latin" => Cataviz::prepare($sql . " AND lang = 'lat'"),
    "Anglais" => Cataviz::prepare($sql . " AND lang = 'eng'"),
    "Allemand" => Cataviz::prepare($sql . " AND lang = 'ger'"),
    // "Ancien-Français" => Cataviz::prepare($sql . " AND lang = 'frm'"),
    // no null, default is French
    // "?" => Cataviz::prepare($sql . " AND lang IS NULL"),
    // "ita" => "Italien",
    // "spa" => "Espagnol",
    // "dut" => "Néerlandais",
    // "frm" => "Ancien-français",
);

$qdoc = Cataviz::prepare("SELECT count(*) AS count FROM doc WHERE year = ? AND lang = ?");


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
// per series infos
echo ',
        "attrs": {
            "y2label":"Moyenne nb de pages",
            "series": {
                "Français": {
                    "plotter": "Dygraph.plotHistory"
                },
                "Latin": {
                    "plotter": "Dygraph.plotHistory"
                },
                "Anglais": {
                    "plotter": "Dygraph.plotHistory"
                },
                "Allemand": {
                    "plotter": "Dygraph.plotHistory"
                },
                "Ancien-Français": {
                    "plotter": "Dygraph.plotHistory"
                }
            }
        }';

echo "\n    }\n";
echo "}\n";
