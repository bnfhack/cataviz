<?php
use Oeuvres\Kit\{Http, Route, Select};



function title()
{
    return "BnF, Catalogue général, classification “Clément”";
}

function main()
{
    $start = Http::int('start', 1715, 1452, 2020);
    $end = Http::int('end', 1788, 1452, 2020);
    $clement = include(dirname(__DIR__) . "/data/clement.php");

?>
<div class="form_chart">
    <form name="form">

        <label>De <input name="start" size="4" value="<?= $start ?>" /></label>
        <label>à <input name="end" size="4" value="<?= $end ?>" /></label>
        <button id="submit" type="submit">▶</button>
    </form>
    <div id="row">
        <div id="terms"></div>
        <div id="chart_frame">
            <div id="chart" class="dygraph"></div>
        </div>
    </div>
</div>
<script type="text/javascript" src="<?= Route::home_href() ?>theme/cataviz.js">//</script>
<script type="text/javascript">

const curveUrl = 'data/curve_clement.php';

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

const termsUp = function() {
    const terms = document.getElementById('terms');
    terms.innerText = '';
    let url = new URL('data/suggest_clement.php', document.location);
    url.search = Suggest.pars(form, 'start', 'end');

    Suggest.loadJson(url, function(json) {
        if (!json) return;
        if (!json.data) return;
        if (!json.data.length) return;
        for (let i=0, len = json.data.length; i < len; i++) {
            let line = Suggest.line('t', json.data[i], Suggest.addInput);
            terms.appendChild(line);
        }
    });

}

const chartUp = function(e) {
    // check if coming from a input
    if (e) {
        const src = e.currentTarget;
        // if delete a term, no need to update term list
        if (src.localName != 'label') termsUp();
    }
    else {
        termsUp();
    }
    // update url params
    const pars = Suggest.pars(form);
    let url = new URL(window.location);
    url.search = pars;
    window.history.pushState({}, '', url);

    url = new URL(curveUrl, document.location);
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
// create form inputs from http params
<?php
// php know label for values
$terms = http::pars('t');
echo "let el = null;\n";
foreach($terms as $t) {
    if (isset($clement[$t])) $label = $clement[$t];
    else $label = $t;
    echo "el = Suggest.input('t', '$t', '$label', chartUp);\n";
    echo "before.parentNode.insertBefore(el, before);\n";
}

?>
chartUp();

</script>
<?php

}
