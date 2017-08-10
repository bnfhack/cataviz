<?php
// header('Content-type: text/plain; charset=utf-8');
include ( dirname(__FILE__).'/Cataviz.php' );
$db = new Cataviz( "databnf.sqlite" );
if (isset($_REQUEST['from'])) $from = $_REQUEST['from'];
else $from = 1760;
if ( $from < 1452 ) $from = 1452;
if ( $from > 2000 ) $from = 2000;
if (isset($_REQUEST['to'])) $to = $_REQUEST['to'];
else $to = 1960;
if ( $to < 1475 ) $to = 2000;
if ( $to > 2000 ) $to = 2000;

if ( isset($_REQUEST['smooth']) ) $smooth = $_REQUEST['smooth'];
else $smooth = 5;
if ( $smooth < 0 ) $smooth = 0;
if ( $smooth > 50 ) $smooth = 50;

$log = true;
if ( isset($_REQUEST['log']) ) $log = $_REQUEST['log'];

?><!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <script src="lib/dygraph.min.js">//</script>
    <link rel="stylesheet" type="text/css" href="lib/dygraph.css"/>
    <link rel="stylesheet" type="text/css" href="cataviz.css"/>
    <style>
    .dygraph-legend { left: 10% !important; top: 0.5em !important; }
    .dygraph-ylabel { color: rgba( 0, 0, 0, 0.7 ); font-weight: normal; }
    .dygraph-axis-label-y2 { color: rgba( 128, 128, 128, 1 ); }
    .dygraph-y2label { color: rgba( 128, 128, 128, 0.5 ); }
    .ann { transform: rotateZ(45deg); transform-origin: 10% 50%; padding-left: 1em; border-left: none !important; border-bottom: 1px solid #000 !important; font-size: 14pt !important; font-weight: normal; }
    </style>
  </head>
  <body>
    <?php include ( dirname(__FILE__).'/menu.php' ) ?>
    <header>
      <div class="links">
        <a href="" target="_new">Data.bnf.fr, auteurs, population </a> 
      </div>
      <form name="dates">
        De <input name="from" size="4" value="<?php echo $from ?>"/>
        à <input name="to" size="4" value="<?php echo  $to ?>"/>
        Échelle
        <button id="log"  disabled="true" type="button">log</button>
        <button id="linear" type="button">linéaire</button>
        <button type="submit">▶</button>
        <button onclick="window.location.href='?'; " type="button">Reset</button>
      </form>
    </header>
    <div id="chart" class="dygraph" style="width:100%; height:400px;"></div>
    <script type="text/javascript">
    g = new Dygraph(
      document.getElementById("chart"),
      [
<?php
$qm = $db->prepare( "SELECT count(*) AS count FROM person WHERE fr = 1 AND birthyear = ? AND gender = 1 " );
$qf = $db->prepare( "SELECT count(*) AS count FROM person WHERE fr = 1 AND birthyear = ? AND gender = 2 " );


for ( $date=$from; $date <= $to; $date++ ) {
  $qm->execute( array( $date ) );
  list( $mcount ) = $qm->fetch( PDO::FETCH_NUM );
  $qf->execute( array( $date ) );
  list( $fcount ) = $qf->fetch( PDO::FETCH_NUM );
  echo "[".$date;
  echo ",".$mcount;
  echo ",".number_format( $fcount, 2, '.', '' );
  echo ",". number_format( 100.0 * $fcount / ($fcount + $mcount), 2, '.', '');
  echo "],\n";
}
       ?>],
      {
        labels: [ "Année", "Hommes", "Femmes", "% femmes" ],
        legend: "always",
        labelsSeparateLines: "true",
        ylabel: "Naissances d’auteurs",
        y2label: "Part des femmes",
        showRoller: true,
        rollPeriod: <?php echo $smooth ?>,
        <?php if ($log) echo "logscale: 'true',\n";  ?>
        series: {
          "Hommes": {
            axis: 'y',
            color: "rgba( 0, 0, 128, 1 )",
            strokeWidth: 2,
          },
          "Femmes": {
            axis: 'y',
            color: "rgba( 255, 128, 128, 1 )",
            strokeWidth: 2,
          },
          "% femmes": {
            axis: 'y2',
            color: "rgba( 128, 128, 128, 0.5)",
            strokeWidth: 4,
          },
        },
        axes: {
          x: {
            gridLineWidth: 2,
            drawGrid: true,
            independentTicks: true,
          },
          y: {
            independentTicks: true,
            drawGrid: true,
            gridLineColor: "rgba( 128, 128, 128, 0.5 )",
            gridLineWidth: 1,
          },
          y2: {
            independentTicks: true,
            drawGrid: true,
            gridLineColor: "rgba( 128, 128, 128, 0.3)",
            gridLineWidth: 2,
            gridLinePattern: [4,4],
          },
        },
        underlayCallback: function(canvas, area, g) {
          canvas.fillStyle = "rgba(255, 255, 102, 0.5)";
          var periods = [[1914,1919]];
          var lim = periods.length;
          for ( var i = 0; i < lim; i++ ) {
            var bottom_left = g.toDomCoords( periods[i][0], -20 );
            var top_right = g.toDomCoords( periods[i][1], +20 );
            var left = bottom_left[0];
            var right = top_right[0];
            canvas.fillRect(left, area.y, right - left, area.h);
          }
        },
      }
    );
    g.ready(function() {
      g.setAnnotations([
        { series: "Hommes", x: "1606", shortText: "1606, Corneille", width: "", height: "", cssClass: "ann", },
        { series: "Hommes", x: "1622", shortText: "1622, Molière", width: "", height: "", cssClass: "ann", },
        { series: "Hommes", x: "1639", shortText: "1639, Racine", width: "", height: "", cssClass: "ann", },
        { series: "Hommes", x: "1694", shortText: "1694, Voltaire", width: "", height: "", cssClass: "ann", },
        { series: "Hommes", x: "1712", shortText: "1712, Rousseau", width: "", height: "", cssClass: "ann", },
        { series: "Hommes", x: "1743", shortText: "1743, Condorcet", width: "", height: "", cssClass: "ann", },
        { series: "Hommes", x: "1783", shortText: "1783, Stendhal", width: "", height: "", cssClass: "ann", },
        { series: "Hommes", x: "1802", shortText: "1802, Hugo", width: "", height: "", cssClass: "ann", },
        { series: "Hommes", x: "1821", shortText: "1821, Baudelaire", width: "", height: "", cssClass: "ann", },
        { series: "Hommes", x: "1840", shortText: "1840, Zola", width: "", height: "", cssClass: "ann", },
        { series: "Hommes", x: "1871", shortText: "1871, Proust", width: "", height: "", cssClass: "ann", },
        { series: "Hommes", x: "1886", shortText: "1886, Fournier", width: "", height: "", cssClass: "ann", },
      ]);
    });
    var linear = document.getElementById("linear");
    var log = document.getElementById("log");
    var setLog = function(val) {
      g.updateOptions({ logscale: val });
      linear.disabled = !val;
      log.disabled = val;
    };
    linear.onclick = function() { setLog(false); };
    log.onclick = function() { setLog(true); };
    </script>
    <p>Population d’auteurs </p>
    <?php include ( dirname(__FILE__).'/footer.php' ) ?>
  </body>
</html>
