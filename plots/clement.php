<?php
use Oeuvres\Kit\{Http, Route, Select};



function title()
{
    return "BnF, Catalogue général, classification “Clément”";
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
        <div id="sugg" data-url="data/suggest_clement.php" data-name="t">
            <nav>

            </nav>
        </div>
        <div id="chart_frame">
            <div id="chart" class="dygraph" data-url="data/curve_clement.php"></div>
        </div>
    </div>
</div>
<script type="text/javascript" src="<?= Route::home_href() ?>theme/cataviz.js">//</script>
<script type="text/javascript">

Cataviz.chartInit('chart', 'form');
Cataviz.suggInit('sugg');
Cataviz.suggUp();
<?php
$js = '';
$js .= "let el = null;\n";
$js .= "const point = Cataviz.chart.form.lastElementChild;\n";
$terms = http::pars('t');
foreach($terms as $t) {
    if (isset($clement[$t])) $label = $clement[$t];
    else $label = $t;
    $js .=  "el = Suggest.input('t', '$t', '$label', Cataviz.chartUp);\n";
    $js .= "point.parentNode.insertBefore(el, point);\n";
}
echo $js;
?>
Cataviz.chartUp();


</script>
<?php

}
