<?php
use Oeuvres\Kit\{Http, Route, Select};

function title()
{
    return "BnF, Catalogue général, parité H%F (à la date de leur premier titre)";
}

function main()
{
    $start = Http::int('start', 1685, 1452, Cataviz::$p['date_max']);
    $end = Http::int('end', 1913, 1452, Cataviz::$p['date_max']);

?>
<div class="form_chart">
    <form name="form">
        De <input class="year" name="start" size="4" value="<?= $start ?>" />
        à <input class="year" name="end" size="4" value="<?= $end ?>" />
        <?php 
        /*
$select = new Select('data', 'select_data');
$select->add('curve_pages', 'Pages');
$select->add('curve_lang', 'Langues');
$select->add('curve_format', 'Formats');
echo $select;
*/
        ?>
        <button type="submit">▶</button>
    </form>
    <div id="row">
        <div id="chart_frame">
            <div id="chart" class="dygraph" data-url="data/curve_docgender.php"></div>
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
Cataviz.dypars.drawPoints = true;
Cataviz.dypars.pointSize = 1;
Cataviz.dypars.strokeWidth = 5;

const start = <?= $start ?>;
if (start < 1788) Cataviz.dypars.historySmooth = 2;
else if (start < 1914) Cataviz.dypars.historySmooth = 1;

Cataviz.chartInit('chart', 'form');
/*
Cataviz.chart.form.data.addEventListener('change', function(e) {
    Cataviz.chart.dataset.url = 'data/' + this.value + '.php';
    Cataviz.chartUp();
});
*/
Cataviz.chartUp();

</script>
<?php

}
