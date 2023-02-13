<?php
use Oeuvres\Kit\{Http, Route, Select};

function title()
{
    return "BnF, Catalogue général, lieux d’édition";
}

function main()
{

    $start = Http::int('start', 1685, 1452, 2019);
    $end = Http::int('end', 1913, 1452, 2019);

?>
<div class="form_chart">
    <form name="form">

        <label>De <input name="start" class="year" size="4" value="<?= $start ?>" /></label>
        <label>à <input name="end" class="year" size="4" value="<?= $end ?>" /></label>
        <button id="submit" type="submit">▶</button>
    </form>
    <div id="row">
        <div id="sugg" data-url="data/suggest_place.php" data-name="t">
            <form>
                <input class="q" name="q" placeholder="Filtrer par lettres"/>
            </form>
            <nav>

            </nav>
        </div>
        <div id="chart_frame">
            <div id="chart" class="dygraph"  data-url="data/curve_place.php"></div>
        </div>
    </div>
</div>
<script type="text/javascript" src="<?= Route::home_href() ?>theme/cataviz.js">//</script>
<script type="text/javascript">

// styling curves
Cataviz.dypars.title = "<?= title() ?>";
Cataviz.dypars.ylabel = "Titres par an";
Cataviz.dypars.drawPoints = true;
Cataviz.dypars.pointSize = 1.5;
Cataviz.dypars.historySmooth = 3;
Cataviz.dypars.strokeWidth = 2;
Cataviz.dypars.plotter = Dygraph.plotHistory;
Cataviz.dypars.logscale = true;

Cataviz.chartInit('chart', 'form');
Cataviz.suggInit('sugg');
Cataviz.suggInputs('t');
Cataviz.suggUp();
Cataviz.chartUp();



</script>
<?php

}
