<?php
require_once(__DIR__ . "/../Cataviz.php");
header("Access-Control-Allow-Origin:*");
// header("Content-Type: application/json");

// have one ore more person nb, by ark ()
// echo Cataviz::$p['pers'];
use Oeuvres\Kit\{Http};

print_r(Http::pars('pers', ['blah', 'blih']));

$pers = Http::pars('pers');
if (!count($pers)) {
    $nb_list = Http::pars('pers', [11928669]);
    // get pers.id by record number
    $sql = "SELECT id FROM pers WHERE nb = ? ";
    for ()
}



?>
