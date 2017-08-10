<?php
// header('Content-type: text/plain; charset=utf-8');
include ( dirname(__FILE__).'/Cataviz.php' );
$db = new Cataviz( "databnf.sqlite" );
if (isset($_REQUEST['from'])) $from = $_REQUEST['from'];
else $from = 1760;
if ( $from < 1452 ) $from = 1452;
if ( $from > 2014 ) $from = 2000;
if (isset($_REQUEST['to'])) $to = $_REQUEST['to'];
else $to = 1960;
if ( $to < 1475 ) $to = 2015;
if ( $to > 2015 ) $to = 2015;

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
    .dygraph-legend { left: 10% !important; top: 0.5em !important; }
    .dygraph-axis-label-y2 { color: rgba( 192, 128, 192, 1 ); }
    .dygraph-y2label { color: rgba( 192, 128, 192, 1 ); }
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
        <button id="log" type="button">log</button>
        <button id="linear" disabled="true" type="button">linéaire</button>
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
$qm = $db->prepare( "SELECT avg(age) FROM person WHERE fr = 1 AND deathyear >= ? AND deathyear <= ? AND gender = 1 " );
$qf = $db->prepare( "SELECT avg(age) FROM person WHERE fr = 1 AND deathyear >= ? AND deathyear <= ? AND gender = 2 " );
$q = $db->prepare( "SELECT count(*) FROM person WHERE fr = 1 AND deathyear = ? " );

$fcount = 0;
$mcount = 0;
$lfage = 0;
for ( $date=$from; $date <= $to; $date++ ) {
  $sigma = 0;
  $qm->execute( array( $date-$sigma, $date+$sigma ) );
  list( $mage ) = $qm->fetch( PDO::FETCH_NUM );
  $sigma = 5;
  $qf->execute( array( $date-$sigma, $date+$sigma ) );
  list( $fage ) = $qf->fetch( PDO::FETCH_NUM );

  $q->execute( array( $date ) );
  list( $count ) = $q->fetch( PDO::FETCH_NUM );
  echo "[".$date;
  echo ",".( $count );
  echo ",". number_format( $mage, 2, '.', '');
  echo ",". number_format( $fage, 2, '.', '');
  echo "],\n";
}
       ?>],
      {
        labels: [ "Année", "Morts", "♂ longévité", "♀ longévité" ],
        legend: "always",
        labelsSeparateLines: "true",
        ylabel: "Nombre de morts",
        y2label: "Âge à la mort",
        showRoller: true,
        rollPeriod: <?php echo $smooth ?>,
        <?php if ($log) echo "logscale: true,";  ?>
        series: {
          "Morts": {
            color: "rgba( 64, 64, 64, 1 )",
            strokeWidth: 1,
            fillGraph: true,
          },
          "Femmes": {
            color: "rgba( 255, 128, 128, 0.7 )",
            strokeWidth: 4,
          },
          "♂ longévité": {
            axis: 'y2',
            color: "rgba( 0, 0, 192, 0.7 )",
            strokeWidth: 4,
          },
          "♀ longévité": {
            axis: 'y2',
            color: "rgba( 255, 128, 128, 0.7 )",
            strokeWidth: 4,
          },
        },
        axes: {
          x: {
            drawGrid: false,
            independentTicks: true,
            gridLineColor: "rgba( 128, 128, 128, 0.5)",
            gridLineWidth: 1,
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
            gridLineColor: "rgba( 192, 128, 160, 1 )",
            gridLineWidth: 1,
          },
        },
        underlayCallback: function(canvas, area, g) {
          canvas.fillStyle = "rgba(255, 128, 0, 0.2)";
          var periods = [ [1789,1794], [1814,1815], [1830,1831], [1848,1849], [1870,1871], [1914,1918], [1939,1945]];
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
