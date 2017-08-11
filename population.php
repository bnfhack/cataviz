<?php
// header('Content-type: text/plain; charset=utf-8');
include ( dirname(__FILE__).'/Cataviz.php' );
$db = new Cataviz( "databnf.sqlite" );
if (isset($_REQUEST['from'])) $from = $_REQUEST['from'];
else $from = 1770;
if ( $from < 1452 ) $from = 1452;
if ( $from > 2014 ) $from = 2000;
if (isset($_REQUEST['to'])) $to = $_REQUEST['to'];
else $to = 1880;
if ( $to < 1475 ) $to = 2016;
if ( $to > 2016 ) $to = 2016;

if ( isset($_REQUEST['smooth']) ) $smooth = $_REQUEST['smooth'];
else $smooth = 0;
if ( $smooth < 0 ) $smooth = 0;
if ( $smooth > 50 ) $smooth = 50;

$log = NULL;
if ( isset($_REQUEST['log']) ) $log = $_REQUEST['log'];

?><!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <script src="lib/dygraph.min.js">//</script>
    <link rel="stylesheet" type="text/css" href="lib/dygraph.css"/>
    <link rel="stylesheet" type="text/css" href="cataviz.css"/>
    <style>
    .dygraph-legend { left: 8% !important; top: 0.5em !important; }
    .dygraph-y2label { color: rgba( 128, 128, 128, 0.5) !important; }
    .dygraph-axis-label-y2 { color: rgba( 128, 128, 128, 1); }
    .dygraph-ylabel { font-weight: normal !important; color: rgba( 0, 0, 0, 0.5) !important; }
    .ann { transform: rotateZ(-90deg); transform-origin: 0% 100%; padding-left: 1em; border-left: none !important; border-bottom: 1px solid #000 !important; font-size: 14pt !important; font-weight: normal; color: rgba( 0, 0, 0, 0.8) !important; }
/*
.dygraph-ylabel { color: rgba( 192, 0, 0, 1 ); font-weight: normal; }
*/
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
        <button id="log" <?php if( $log ) echo'disabled="true"';?> type="button">log</button>
        <button id="linear" <?php if( !$log ) echo'disabled="true"';?> type="button">linéaire</button>
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
$qf = $db->prepare( "SELECT count(*) AS count FROM person WHERE fr = 1 AND opus1 <= ? AND ( deathyear >= ? OR deathyear IS NULL ) AND gender = 2 " );
$qm = $db->prepare( "SELECT count(*) AS count FROM person WHERE fr = 1 AND opus1 <= ? AND ( deathyear >= ? OR deathyear IS NULL ) AND gender = 1 " );

$fcount = 0;
$mcount = 0;
for ( $date=$from; $date <= $to; $date++ ) {
  $qf->execute( array( $date, $date ) );
  list( $fcount ) = $qf->fetch( PDO::FETCH_NUM );
  $qm->execute( array( $date, $date ) );
  list( $mcount ) = $qm->fetch( PDO::FETCH_NUM );
  echo "[".$date;
  echo ",".( $mcount);
  echo ",".$fcount;
  echo ",". number_format( ( 100.0 * $fcount / ($mcount + $fcount) ), 2, '.', '');
  echo "],\n";
}

/*
"Moy. pages": {
  axis: 'y2',
  color: "rgba( 128, 128, 128, 0.5)",
  strokeWidth: 5,
},

*/
       ?>],
      {
        labels: [ "Année", "Hommes", "Femmes", "% femmes" ],
        legend: "always",
        labelsSeparateLines: "true",
        ylabel: "Auteurs vivants",
        y2label: "% femmes",
        <?php if ($log) echo "logscale: true,";  ?>
        showRoller: true,
        rollPeriod: <?php echo $smooth ?>,
        series: {
          "Hommes": {
            color: "rgba( 0, 0, 192, 1 )",
            strokeWidth: 4,
          },
          "Femmes": {
            color: "rgba( 255, 128, 128, 1 )",
            strokeWidth: 4,
          },
          "% femmes": {
            axis: 'y2',
            color: "rgba( 64, 64, 64, 1 )",
            strokeWidth: 1,
            fillGraph: true,
          },
        },
        axes: {
          x: {
            // gridLineWidth: 2,
            drawGrid: false,
            independentTicks: true,
          },
          y: {
            independentTicks: true,
            drawGrid: true,
            gridLineColor: "rgba( 128, 128, 128, 0.5)",
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
          canvas.fillStyle = "rgba(255, 128, 0, 0.2)";
          var periods = [ [1789,1795], [1814,1815], [1830,1831], [1848,1849], [1870,1871], [1914,1918], [1939,1945]];
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
        { series: "% femmes", x: "1648", shortText: "La Fronde", width: "", height: "", cssClass: "ann", },
        { series: "% femmes", x: "1789", shortText: "1789", width: "", height: "", cssClass: "ann", },
        { series: "% femmes", x: "1815", shortText: "1815", width: "", height: "", cssClass: "ann", },
        { series: "% femmes", x: "1830", shortText: "1830", width: "", height: "", cssClass: "ann", },
        { series: "% femmes", x: "1848", shortText: "1848", width: "", height: "", cssClass: "ann", },
        { series: "% femmes", x: "1870", shortText: "1870", width: "", height: "", cssClass: "ann", },
        { series: "% femmes", x: "1914", shortText: "1914", width: "", height: "", cssClass: "ann", },
        { series: "% femmes", x: "1939", shortText: "1939", width: "", height: "", cssClass: "ann", },
        { series: "% femmes", x: "1968", shortText: "1968", width: "", height: "", cssClass: "ann", },
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
