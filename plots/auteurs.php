<?php
use Oeuvres\Kit\{Http, Route, Select};

function title()
{
    return "BnF, Catalogue général, auteurs, éditions";
}

function main()
{

    $start = Http::int('start', 1685, 1452, 2019);
    $end = Http::int('end', 1913, 1452, 2019);
?>
<div class="form_chart">
    <form name="form" class="line">

        <label>De <input name="start" size="4" value="<?= $start ?>" /></label>
        <label>à <input name="end" size="4" value="<?= $end ?>" /></label>
        <button id="submit" type="submit">▶</button>
    </form>
    <div id="row">
        <div id="sugg" data-url="data/suggest_contrib.php" data-name="pers">
            <form>
                <input name="q" class="q" placeholder="Filtrer par lettres"/>
                <div>Né
                après <input name="after" size="4"/>
                avant <input name="before" size="4"/>
                </div>
            </form>
            <nav>

            </nav>
        </div>
        <div id="chart_frame">
            <div id="chart" class="dygraph" data-url="data/curve_contrib.php"></div>
        </div>
    </div>
</div>
<script type="text/javascript" src="<?= Route::home_href() ?>theme/cataviz.js">//</script>
<script type="text/javascript">

const chartUrl = "data/curve_contrib.php";

Cataviz.dypars.title = "<?= title() ?>";
Cataviz.dypars.ylabel = "Titres par an";
Cataviz.dypars.drawPoints = true;
Cataviz.dypars.pointSize = 1.5;
Cataviz.dypars.historySmooth = 3;
Cataviz.dypars.strokeWidth = 2;
Cataviz.dypars.plotter = Dygraph.plotHistory;

Cataviz.chartInit('chart', 'form');
Cataviz.suggInit('sugg');
Cataviz.suggUp();
<?= Cataviz::url_pers() ?>
Cataviz.chartUp();


</script>
<?php

}
