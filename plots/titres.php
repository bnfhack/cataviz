<?php
use Oeuvres\Kit\{Route, Select};

function title()
{
    return "BnF, Catalogue général, titres par année";
}

function main()
{

?>
<div class="form_chart">
    <form name="form">
        De <input name="from" size="4" value="<?= Cataviz::$p['from'] ?>" />
        à <input name="to" size="4" value="<?= Cataviz::$p['to'] ?>" />
        <?php 
$select = new Select('data', 'select_data');
$select->add('doc_pages', 'Pages');
$select->add('doc_langue', 'Langues');
$select->add('doc_format', 'Formats');
echo $select;
        ?>
        <button type="submit">▶</button>
    </form>
    <div id="row">
        <div id="chart" class="dygraph"></div>
        <!--
        <div id="doc">
        </div>
        -->
    </div>
</div>

<script type="text/javascript" src="<?= Route::home_href() ?>theme/cataviz.js">//</script>

<script type="text/javascript">
attrs.title = "<?= title() ?>";
attrs.ylabel = "Titres par an";


attrs.drawPoints = true;
attrs.pointSize = 1.5;
attrs.strokeWidth = 10;
attrs.logscale = true;

const from = <?= Cataviz::$p['from'] ?>;
if (from < 1788) attrs.historySmooth = 2;
else if (from < 1914) attrs.historySmooth = 1;
const form = document.forms['form'];
form.chart = document.getElementById("chart");
form.dygraph = function() {
    // update url params
    const locbar = new URL(window.location);
    locbar.searchParams.set('from', form.from.value);
    locbar.searchParams.set('to', form.to.value);
    locbar.searchParams.set('data', form.data.value);
    window.history.pushState({}, '', locbar);

    let src = form.data.value;
    if (!src) src = 'doc_lang'; 
    let url = 'data/' + src + '.php';
    url += "?from=" + form.from.value + "&to=" + form.to.value;

    Formajax.loadJson(url, function(json) {
        if (!attrs.series) attrs.series = {};
        // var annoteSeries = json.meta.labels[1]; // period anotations
        attrs.labels = json.meta.labels;
        if (json.meta.attrs) {
            Object.assign(attrs, json.meta.attrs);
        }
        // set plotter
        for(var key in attrs.series){
            let serie = attrs.series[key];
            if (!serie['plotter'] || typeof serie['plotter'] !== 'string') continue;
            // string 2 function, recursive
            let fun = window;
            const methods = serie['plotter'].split(".");
            for(var i in methods) {
                fun = fun[methods[i]];
            }
            attrs.series[key]['plotter'] = fun;
        }
        attrs.series["Tout"]= {
            "pointSize" : 1.5,
            "drawPoints": true,
            "color": "#ccc",
        };

        g = new Dygraph(form.chart, json.data, attrs);
        g.ready(function() {
            g.setAnnotations(attrs.annotations("Tout"));
        });
    });
}
form.start.onchange = form.dygraph;
form.end.onchange = form.dygraph;
form.data.onchange = form.dygraph;
form.dygraph();


</script>
<?php

}
