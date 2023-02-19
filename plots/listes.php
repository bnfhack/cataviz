<?php
use Oeuvres\Kit\{Http, Route, Select};



function title()
{
    return "“Clément”, liste, BnF, catalogue général";
}

function main()
{
    $start = Http::int('start', 1715, 1452, 1995);
    $end = Http::int('end', 1788, 1452, 1995);
    $clement = include(dirname(__DIR__) . "/data/clement.php");

?>
<div class="form_chart">
    <form name="form" class="line">

        <label>De <input name="start" class="year" size="4" value="<?= $start ?>" /></label>
        <label>à <input name="end" class="year" size="4" value="<?= $end ?>" /></label>
        <button id="submit" type="submit">▶</button>
    </form>
    <div id="row">
        <div id="sugg" data-url="data/suggest_clement.php">
            <nav>

            </nav>
        </div>
        <div id="authors" data-url="data/suggest_clemauth.php">
            <nav>

            </nav>
        </div>
    </div>
</div>
<script type="text/javascript" src="<?= Route::home_href() ?>theme/cataviz.js">//</script>
<script type="text/javascript">

Cataviz.suggInit('sugg');
Cataviz.suggUp();



</script>
<?php

}
