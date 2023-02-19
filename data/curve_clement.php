<?php
require_once(__DIR__ . "/../Cataviz.php");
$clement = include(__DIR__ . "/clement.php");

use Oeuvres\Kit\{Http};

$start_time = microtime(true);
header("Access-Control-Allow-Origin:*");
header("Content-Type: application/json");

$start = Http::int('start', 1715, 1452, 1995);
$end = Http::int('end', 1788, 1452, 1995);

$tout = "Tout";
$sql = "SELECT count(*) AS count FROM doc WHERE year = ? AND clement = ? ";
$q = Cataviz::prepare($sql);
$sql = "SELECT count(*) AS count FROM doc WHERE year = ? AND clement IS NULL";
$q_null = Cataviz::prepare($sql);
$sql = "SELECT count(*) AS count FROM doc WHERE year = ? ";
$q_all = Cataviz::prepare($sql);


// the grouping values
$terms = Http::pars('t');
// value if no point
$none = 1; // log

echo "{\n";
echo '    "data":[';
$first = true;
for ($year = $start; $year <= $end; $year++) {
    if ($first) $first = false;
    else echo ","; 
    echo "\n        ";
    $line = [];
    $line[] = $year;

    // all in bg
    $q_all->execute([$year]);
    list($count) = $q_all->fetch(PDO::FETCH_NUM);
    $line[] = intval($count);
    
    foreach ($terms as $t) {
        if (!$t || $t == '?') {
            $q_null->execute([$year]);
            list($count) = $q_null->fetch(PDO::FETCH_NUM);
        }
        else {
            $q->execute([$year, $t]);
            list($count) = $q->fetch(PDO::FETCH_NUM);
        }
        if (!$count) $count = $none;
        $line[] = intval($count);
    }
    echo json_encode($line, JSON_UNESCAPED_UNICODE);
    flush();
}
echo "\n    ],\n";
echo '    "meta":{'."\n";

echo '        "labels": ["Année"';
echo ', "' .$tout . '"';
foreach ($terms as $t) {
    echo ', "';
    if (!$t) echo '?';
    else if (isset($clement[$t])) echo $clement[$t];
    else echo $t;
    echo '"';
}
echo "]";
$historySmooth = 0;
if ($start < 1815) $historySmooth = 1;
$attrs = [
    "title" => "BnF, Catalogue général, classification “Clément”",
    "y2label" => "Titres par classe “Clément”",    
    "ylabel" => "Titres total",
    'drawPoints' => true,
    'pointSize' => 5,
    'strokeWidth' => ($start < 1750)?2:1,
    "logscale" => true,
    'historySmooth' => $historySmooth,
    "series" => [],
];

$attrs['series'][$tout] = [
    "axis" => "y1",
    "color" => 'rgba(0, 0, 0, 0.3)',
    "fillAlpha" => 0.2,
    'fillGraph' => true,
    'drawPoints' => false,
    'strokeWidth' => 0.5,
    /*
    'strokeBorderWidth' => 0.3, 
    'strokeBorderColor' => "rgba(255, 255, 255, 0.3)",
    */
];


foreach ($terms as $t) {
    if (isset($clement[$t])) $label = $clement[$t];
    else $label = $t;
    $attrs['series'][$label] = [
        "axis" => "y2",
        "plotter" => "Dygraph.plotHistory",
    ];
}

echo ',
        "attrs": ' . json_encode($attrs);


echo ', 
        "time": "'. (microtime(true) - $start_time) * 1000 . 'ms."';
echo "\n    }\n";
echo "}\n";
