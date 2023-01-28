<?php
use Oeuvres\Kit\{Select};

function title()
{
    return "BnF, Catalogue général, démographie des auteurs";
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
$select->add('pers_doc1', '“Naissances”');

echo $select;
        ?>
        <button type="submit">▶</button>
    </form>
    <div id="row">
        <div id="chart" class="dygraph"></div>
        <div id="doc">
           <?php include(dirname(__DIR__) . '/html/titres.html') ; ?>
        </div>
    </div>
</div>
<script type="text/javascript">
attrs.title = "<?= title() ?>";
attrs.ylabel = "Titres par an";

// attrs.stackedGraph = true;
// attrs.plotter = Dygraph.plotHistory;
attrs.strokeWidth = 10;
attrs.pointSize = 1;
// attrs.strokeWidth = 1;
attrs.logscale = true;


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
            console.log(attrs.series[key]['plotter']);
        }
        attrs.series["Tout"]= {
            "pointSize": 0,
            "color": "#ccc",
        };

        g = new Dygraph(form.chart, json.data, attrs);
        g.ready(function() {
            g.setAnnotations(attrs.annotations("Tout"));
        });
    });
}
form.from.onchange = form.dygraph;
form.to.onchange = form.dygraph;
form.data.onchange = form.dygraph;
form.dygraph();


</script>
<?php

}
