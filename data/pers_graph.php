<?php
require_once(__DIR__ . "/../Cataviz.php");
header("Access-Control-Allow-Origin:*");
// header("Content-Type: application/json");

// have one ore more person nb, by ark ()
// echo Cataviz::$p['pers'];
use Oeuvres\Kit\{Http};

print_r(Http::pars('pers', ['blah', 'blih']));


?>
<form method="post">
    <input name="pers"/>
    <input name="pers"/>
    <input name="pers"/>
    <button type="submit">GO</button>
</form>