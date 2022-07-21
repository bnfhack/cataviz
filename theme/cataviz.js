let attrs = {
    legend: "always",
    // labelsSeparateLines: true,
    showRoller: false,
    titleHeight: 75,
};

attrs.colors = [
    'hsla(0, 50%, 50%, 1)', // 1
    'hsla(225, 50%, 50%, 1)', // 2
    'hsla(90, 60%, 30%, 1)', // 3
    'hsla(45, 80%, 50%, 1)', // 4
    'hsla(180, 50%, 40%, 1)', // 5
    'hsla(270, 50%, 50%, 1)', // 6
    'hsla(135, 70%, 50%, 1)',
    'hsla(215, 90%, 50%, 1)',
    'hsla(0, 30%, 50%, 1)',
];



attrs.underlayCallback = function(canvas, area, g) {
    canvas.fillStyle = "rgba(192, 192, 192, 0.2)";
    var periods = [
        [1562, 1598],
        [1648, 1653],
        [1789, 1795],
        [1814, 1815],
        [1830, 1831],
        [1848, 1849],
        [1870, 1871],
        [1914, 1918],
        [1939, 1945]
    ];
    var lim = periods.length;
    for (var i = 0; i < lim; i++) {
        var bottom_left = g.toDomCoords(periods[i][0], -20);
        var top_right = g.toDomCoords(periods[i][1], +20);
        var left = bottom_left[0];
        var right = top_right[0];
        canvas.fillRect(left, area.y, right - left, area.h);
    }
};



attrs.legend = "always";
attrs.axes = {
    x: {
        gridLineWidth: 1,
        gridLineColor: "rgba(64, 64, 64, 0.7)",
        gridLinePattern: [1, 5],
        drawGrid: true,
        independentTicks: true,
        /*
        ticker: function(a, b, pixels, opts, dygraph, vals) {
            return [
                { "v": 1648, "label": 1648 },
                { "v": 1685, "label": 1685 },
                { "v": 1715, "label": 1715 },
                { "v": 1756, "label": "1756        " },
                { "v": 1763, "label": "      1763" },
                { "v": 1789, "label": "1789        " },
                { "v": 1795, "label": "        1795" },
                { "v": 1815, "label": 1815 },
                { "v": 1830, "label": 1830 },
                { "v": 1848, "label": 1848 },
                { "v": 1870, "label": 1870 },
                { "v": 1900, "label": 1900 },
                { "v": 1914, "label": "1914        " },
                { "v": 1918, "label": "        1918" },
                { "v": 1939, "label": "1939        " },
                { "v": 1945, "label": "        1945" },
                { "v": 1968, "label": 1968 },
                { "v": 1989, "label": 1989 },
                { "v": 2005, "label": 2005 },
                { "v": 2018, "label": 2019 },
            ];
        }
        */
    },
    y: {
        independentTicks: true,
        drawGrid: true,
        gridLineColor: "rgba(64, 64, 64, 0.7)",
        gridLineWidth: 0.5,
    },
    y2: {
        drawGrid: false,
        independentTicks: true,
        gridLineColor: "rgba(192, 192, 192, 0.4)",
        gridLineWidth: 4,
        gridLinePattern: [6, 6],
    },
};

/*
<? php if ($log) echo "attrs.logscale = true;"; ?>
attrs.rollPeriod = <? php echo $smooth ?>;
*/


/* todo
if (window.annoteSeries) {
    g.ready(function() {
        g.setAnnotations([
            { series: annoteSeries, x: "1562", shortText: "Guerres de Religion", width: "", height: "", cssClass: "annv" },
            { series: annoteSeries, x: "1648", shortText: "La Fronde", width: "", height: "", cssClass: "annv" },
            { series: annoteSeries, x: "1789", shortText: "1789", width: "", height: "", cssClass: "annv" },
            { series: annoteSeries, x: "1815", shortText: "1815", width: "", height: "", cssClass: "annv" },
            { series: annoteSeries, x: "1830", shortText: "1830", width: "", height: "", cssClass: "annv" },
            { series: annoteSeries, x: "1848", shortText: "1848", width: "", height: "", cssClass: "annv" },
            { series: annoteSeries, x: "1870", shortText: "1870", width: "", height: "", cssClass: "annv" },
            { series: annoteSeries, x: "1914", shortText: "1914", width: "", height: "", cssClass: "annv" },
            { series: annoteSeries, x: "1939", shortText: "1939", width: "", height: "", cssClass: "annv" },
        ]);
    });
}
*/
/*
var linear = document.getElementById("linear");
var log = document.getElementById("log");
var setLog = function(val) {
    g.updateOptions({ logscale: val });
    linear.disabled = !val;
    log.disabled = val;
};
if (linear) linear.onclick = function() { setLog(false); };
if (log) log.onclick = function() { setLog(true); };
*/