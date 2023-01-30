<?php
require_once(__DIR__ . "/../Cataviz.php");
use Oeuvres\Kit\{Http, Route};

$start_time = microtime(true);
header("Access-Control-Allow-Origin:*");

?><!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8"/>
        <link rel="stylesheet" type="text/css" href="../<?= Route::home_href() ?>theme/cataviz.css"/>
    </head>
    <body>
<?php

// have one ore more person nb, by ark ()


$pers_id = Http::par('pers');
if (!$pers_id) {
    return;
}

$sql = "SELECT * FROM pers WHERE id = ?";
$pers_q = Cataviz::prepare($sql);
$pers_q->execute([$pers_id]);
$pers_row = $pers_q->fetch(PDO::FETCH_ASSOC);

print "<h1>";
print $pers_row['name'];
if ($pers_row['given']) print ", " . $pers_row['given'];
if ($pers_row['role']) print ", " . $pers_row['role'];
if ($pers_row['birthyear'] || $pers_row['deathyear']) {
  print " (";
  if ($pers_row['birthyear']) print $pers_row['birthyear'];
  else print " ";
  print " – ";
  if ($pers_row['deathyear']) print $pers_row['deathyear'];
  else print " ";
  print ")";
}
print "</h1>";
print "<p>";
print $pers_row['note'];
print "</p>";

# list docs for an authour
# query on doc
$sql = "SELECT * FROM doc WHERE id = ?";
$doc_q = Cataviz::prepare($sql);
# loop on contrib
$sql = "SELECT doc FROM contrib WHERE pers = ? ORDER BY year ASC NULLS LAST";
$contrib_q = Cataviz::prepare($sql);
$contrib_q->execute([$pers_id]);

$n = 0;
while ($contrib_row = $contrib_q->fetch(PDO::FETCH_ASSOC)) {
    $doc_q->execute([$contrib_row['doc']]);
    $doc_row = $doc_q->fetch(PDO::FETCH_ASSOC);
    print '<div class="bibl">';
    print ++$n . ". ";
    print $doc_row['title'];
    print ' (';
    print $doc_row['year'] . ", ";
    print $doc_row['place'];
    print ", " . $doc_row['publisher'];
    print ')';
    print '</div>' . "\n";
}

?>
    <body>
</html>