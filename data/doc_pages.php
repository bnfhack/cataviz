<?php
require_once(__DIR__ . "/../Cataviz.php");
header("Access-Control-Allow-Origin:*");
header("Content-Type: application/json");

$sql = "SELECT count(*) AS count FROM doc WHERE year = ? AND ";
$queries = array(
    // "2 p." => Cataviz::prepare($sql."pages <= 2"),
    "]1, 64] p." => Cataviz::prepare($sql."pages <= 64"),
    "]64, 192] p." => Cataviz::prepare($sql."pages > 64 AND pages <= 192"),
    "]192, …] p." => Cataviz::prepare($sql."pages > 192"),
    // "]512, …] p." => Cataviz::prepare($sql."pages > 512"),
    "? p." => Cataviz::prepare($sql."pages IS NULL"),
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
echo "\n    }\n";
echo "}\n";
