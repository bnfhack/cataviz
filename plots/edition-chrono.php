<?php
use Oeuvres\Kit\{Http, Route, Select};

function title()
{
    return "BnF, Catalogue général, lieux d’édition";
}

function main()
{

    $start = Http::int('start', 1685, 1452, 2020);
    $end = Http::int('end', 1913, 1452, 2020);

?>
<div class="form_chart">
    <form name="form">

        <label>De <input name="start" size="4" value="<?= $start ?>" /></label>
        <label>à <input name="end" size="4" value="<?= $end ?>" /></label>
        <input placeholder="Chercher un terme" type="text" class="suggest" data-url="data/suggest_edition.php" id="t" data-name="t"/>
        <button id="submit" type="submit">▶</button>
    </form>
    <div id="row" style="background-color: #000; color: rgba(255, 255, 255, 0.6)" >
        <div style="background-color: #000; color: rgba(255, 255, 255, 0.6)" id="chart" class="dygraph"></div>
    </div>
</div>
<script type="text/javascript" src="<?= Route::home_href() ?>theme/cataviz.js">//</script>
<script type="text/javascript">

attrs.title = "<?= title() ?>";
attrs.ylabel = "Titres par an";

// styling curves
attrs.drawPoints = true;
attrs.pointSize = 1.5;
attrs.historySmooth = 3;

attrs.strokeWidth = 2;
attrs.plotter = Dygraph.plotHistory;

attrs.logscale = true;

const form = document.forms['form'];
form.chart = document.getElementById("chart");

const chartUp = function() {
    // update url params
    const pars = Suggest.pars(form);
    let url = new URL(window.location);
    url.search = pars;
    window.history.pushState({}, '', url);

    url = new URL('data/curve_edition.php', document.location);
    url.search = pars;

    Suggest.loadJson(url, function(json) {
        if (!attrs.series) attrs.series = {};
        // var annoteSeries = json.meta.labels[1]; // period anotations
        attrs.labels = json.meta.labels;
        if (json.meta.attrs) {
            Object.assign(attrs, json.meta.attrs);
        }
        g = new Dygraph(form.chart, json.data, attrs);
    });
}
form.start.onchange = chartUp;
form.end.onchange = chartUp;
const beforeId = 'submit';
const before = document.getElementById(beforeId);
const url = new URL(window.location.href); 
for (const name of url.searchParams.getAll('name')) {
    let el = Suggest.input('name', name, name, chartUp);
    before.parentNode.insertBefore(el, before);
}
chartUp();

</script>
<?php

}
