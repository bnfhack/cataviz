<?php
require_once(__DIR__ . "/../Cataviz.php");
use Oeuvres\Kit\{Http};

header("Access-Control-Allow-Origin:*");
header("Content-Type: application/json");

$start = Http::int('start', 1685, 1452, Cataviz::$p['date_max']);
$end = Http::int('end', 1913, 1452, Cataviz::$p['date_max']);

$sql = "SELECT count(*) AS count FROM doc WHERE year = ? ";


$queries = array(
    "Tout" => Cataviz::prepare($sql),
    "% Français" => Cataviz::prepare($sql . " AND lang = 'fre'"),
    "% Latin" => Cataviz::prepare($sql . " AND lang = 'lat'"),
    "% Anglais" => Cataviz::prepare($sql . " AND lang = 'eng'"),
    "% Allemand" => Cataviz::prepare($sql . " AND lang = 'ger'"),
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
for ($year = $start; $year <= $end; $year++) {
    if ($first) $first = false;
    else echo ","; 
    echo "\n        [" . $year;
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
            'strokeWidth' => ($start < 1750)?2:1,
            'drawPoints' => true,
            'pointSize' => 5,
            "plotter" => "Dygraph.plotHistory",
        ];
    
    }
}


$attrs = [
    "title" => "BnF, catalogue général, part des langues",
    "ylabel" => "Nombre de titres par an",
    "y2label" => "% des titres d’une année",
    "rollPeriod" => 0,
    "strokeWidth" => 0,
    "fillAlpha" => 0.7,
    'historySmooth' => ($start < 1750)?1:0,
    'logscale' => false,
    "series" => $series,
];
echo ',
        "attrs": ' . json_encode($attrs);


echo "\n    }\n";
echo "}\n";
