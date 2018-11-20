attrs.underlayCallback = function(canvas, area, g) {
  canvas.fillStyle = "rgba(192, 192, 192, 0.2)";
  var periods = [[1562,1598], [1648,1653], [1789,1795], [1814,1815], [1830,1831], [1848,1849], [1870,1871], [1914,1918], [1939,1945]];
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
attrs.titleHeight = 35;
attrs.axes = {
  x: {
    gridLineWidth: 4,
    drawGrid: true,
    independentTicks: true,
    gridLineColor: "rgba(192, 192, 192, 0.2)",
  },
  y: {
    independentTicks: true,
    drawGrid: true,
    gridLineColor: "rgba(64, 64, 64, 0.7)",
    gridLineWidth: 0.5,
  },
  y2: {
    independentTicks: true,
    drawGrid: false,
    gridLineColor: "rgba(192, 192, 192, 0.4)",
    gridLineWidth: 4,
    gridLinePattern: [6,6],
  },
};
<?php if ($log) echo "attrs.logscale = true;"; ?>
attrs.showRoller = true;
attrs.rollPeriod = <?php echo $smooth ?>;
attrs.labelsSeparateLines = true;



g = new Dygraph(document.getElementById("chart"), data, attrs);
if (window.annoteSeries) {
  g.ready(function() {
    g.setAnnotations([
      {series: annoteSeries, x: "1562", shortText: "Guerres de Religion", width: "", height: "", cssClass: "annv"},
      {series: annoteSeries, x: "1648", shortText: "La Fronde", width: "", height: "", cssClass: "annv"},
      {series: annoteSeries, x: "1789", shortText: "1789", width: "", height: "", cssClass: "annv"},
      {series: annoteSeries, x: "1815", shortText: "1815", width: "", height: "", cssClass: "annv"},
      {series: annoteSeries, x: "1830", shortText: "1830", width: "", height: "", cssClass: "annv"},
      {series: annoteSeries, x: "1848", shortText: "1848", width: "", height: "", cssClass: "annv"},
      {series: annoteSeries, x: "1870", shortText: "1870", width: "", height: "", cssClass: "annv"},
      {series: annoteSeries, x: "1914", shortText: "1914", width: "", height: "", cssClass: "annv"},
      {series: annoteSeries, x: "1939", shortText: "1939", width: "", height: "", cssClass: "annv"},
    ]);
  });
}
var linear = document.getElementById("linear");
var log = document.getElementById("log");
var setLog = function(val) {
  g.updateOptions({logscale: val});
  linear.disabled = !val;
  log.disabled = val;
};
if (linear) linear.onclick = function() { setLog(false); };
if (log) log.onclick = function() { setLog(true); };
