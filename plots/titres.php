<?php
use Oeuvres\Kit\{Http, Route, Select};

function title()
{
    return "BnF, Catalogue général, titres par année";
}

function main()
{
    $start = Http::int('start', 1685, 1452, 2020);
    $end = Http::int('end', 1913, 1452, 2020);

?>
<div class="form_chart">
    <form name="form">
        De <input class="year" name="start" size="4" value="<?= $start ?>" />
        à <input class="year" name="end" size="4" value="<?= $end ?>" />
        <?php 
$select = new Select('data', 'select_data');
$select->add('curve_format', 'Formats');
$select->add('curve_order', 'Livre 1er, 2nd, 3e');
$select->add('curve_lang', 'Langues');
$select->add('curve_pages', 'Pages');
echo $select;
$data = Http::par('data', 'curve_format');
        ?>
        <button type="submit">▶</button>
    </form>
    <div id="row">
        <div id="chart_frame">
            <div id="chart" class="dygraph" data-url="data/<?= $data ?>.php"></div>
        </div>
        <!--
        <div id="doc">
        </div>
        -->
    </div>
</div>

<script type="text/javascript" src="<?= Route::home_href() ?>theme/cataviz.js">//</script>

<script type="text/javascript">


Cataviz.dypars.title = "<?= title() ?>";
Cataviz.dypars.ylabel = "Titres par an";
Cataviz.dypars.drawPoints = true;
Cataviz.dypars.pointSize = 2;
Cataviz.dypars.strokeWidth = 1;

const start = <?= $start ?>;
if (start < 1788) Cataviz.dypars.historySmooth = 2;
else if (start < 1914) Cataviz.dypars.historySmooth = 1;

/*
attrs.series["Tout"]= {
            "pointSize" : 1.5,
            "drawPoints": true,
            "color": "#ccc",
        };

        g = new Dygraph(form.chart, json.data, attrs);
        g.ready(function() {
            g.setAnnotations(attrs.annotations("Tout"));
        });
*/
Cataviz.chartInit('chart', 'form');
Cataviz.chart.form.data.addEventListener('change', function(e) {
    Cataviz.chart.dataset.url = 'data/' + this.value + '.php';
    Cataviz.chartUp();
});
Cataviz.chartUp();

</script>
<?php

}
