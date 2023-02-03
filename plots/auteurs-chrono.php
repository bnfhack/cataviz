<?php
use Oeuvres\Kit\{Http, Route, Select};

function title()
{
    return "BnF, Catalogue général, auteurs, titres par année";
}

function main()
{

?>
<div class="form_chart">
    <form name="form">

        <label>De <input name="from" size="4" value="<?= Cataviz::$p['from'] ?>" /></label>
        <label>à <input name="to" size="4" value="<?= Cataviz::$p['to'] ?>" /></label>
        <input placeholder="Chercher un auteur" type="text" class="suggest" data-url="data/pers_suggest.php" id="pers" data-name="pers"/>
<?php
$pers_http = Http::pars('pers');
$sql = "SELECT * FROM pers WHERE id = ?";
$pers_q = Cataviz::prepare($sql);
for ($i=0, $len=count($pers_http); $i < $len; $i++) {
    $id = $pers_http[$i];
    $pers_q->execute([$id]);
    $pers_row = $pers_q->fetch();
    if (!$pers_row) continue;
    $label = Cataviz::pers_label($pers_row);
    echo '<label onclick="this.parentNode.removeChild(this); chartUp();" class="pers" title="' . $label .'"><a class="inputDel">🞭</a><input name="pers" type="hidden" value="' . $id .'">' . $label . '</label>';
}
?>
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


const form = document.forms['form'];
form.chart = document.getElementById("chart");

const chartUp = function() {
    // update url params
    const pars = Suggest.pars(form);
    let url = new URL(window.location);
    url.search = pars;
    window.history.pushState({}, '', url);

    url = new URL('data/pers_docs.php', document.location);
    url.search = pars;

    Suggest.loadJson(url, function(json) {
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

        g = new Dygraph(form.chart, json.data, attrs);
    });
}
form.from.onchange = chartUp;
form.to.onchange = chartUp;
chartUp();

</script>
<?php

}
