<?php
require_once(__DIR__ . "/../Cataviz.php");

header("Access-Control-Allow-Origin:*");
header("Content-Type: application/json");

$sql = "SELECT count(*) AS count FROM doc WHERE year = ? AND ";


$queries = array(
    // "?" => Cataviz::prepare($sql."lang IS NULL"),
    "Français" => Cataviz::prepare($sql."lang = 'fre'"),
    "Latin" => Cataviz::prepare($sql."lang = 'lat'"),
    "Anglais" => Cataviz::prepare($sql."lang = 'eng'"),
    "Allemand" => Cataviz::prepare($sql."lang = 'ger'"),
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
echo "\n    }\n";
echo "}\n";
