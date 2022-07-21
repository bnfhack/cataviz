<?php
require_once(__DIR__ . "/../Cataviz.php");

header("Access-Control-Allow-Origin:*");
header("Content-Type: application/json");
$langs = array(
    "fre" => "Français",
    "lat" => "Latin",
    "eng" => "Anglais",
    "ger" => "Allemand",
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
    foreach ($langs as $lang => $label) {
        $qdoc->execute(array($year, $lang));
        list($val) = $qdoc->fetch(PDO::FETCH_NUM);
        echo ", " . $val;
    }
    echo "]";
}
echo "\n    ],\n";
echo '    "meta":{'."\n";

//    labels: [ "Année", "Titres", "PNB/h"],
echo '        "labels": ["Année"';
foreach ($langs as $lang => $label) {
    echo ', "' . $label . '"';
}
echo "]";
echo "\n    }\n";
echo "}\n";
