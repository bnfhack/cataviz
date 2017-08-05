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
    .dygraph-legend { left: 40% !important; top: 1.5em !important; }
/*
.dygraph-ylabel { color: rgba( 192, 0, 0, 1 ); font-weight: normal; }
.dygraph-y2label { color: rgba( 128, 128, 128, 0.5); }
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
$qf = $db->prepare( "SELECT count(*), avg( ) AS count FROM person WHERE fr = 1 AND deathyear = ? AND gender = 2 " );
$qm = $db->prepare( "SELECT count(*) AS count FROM person WHERE fr = 1 AND opus1 <= ? AND ( deathyear >= ? OR deathyear IS NULL ) AND gender = 1 " );

$fcount = 0;
$mcount = 0;
for ( $date=$from; $date <= $to; $date++ ) {
  $qf->execute( array( $date, $date ) );
  list( $fcount ) = $qf->fetch( PDO::FETCH_NUM );
  $qm->execute( array( $date, $date ) );
  list( $mcount ) = $qm->fetch( PDO::FETCH_NUM );
  echo "[".$date;
  echo ",".( $mcount + $fcount );
  echo ",".$fcount;
  echo ",". ( 100.0 * ($mcount + $fcount) / $count );
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
        labels: [ "Année", "Total", "Femmes", "% femmes" ],
        legend: "always",
        labelsSeparateLines: "true",
        ylabel: "Nombre",
        // showRoller: true,
        rollPeriod: <?php echo $smooth ?>,
        series: {
          "Total": {
            color: "rgba( 0, 0, 0, 1 )",
            strokeWidth: 2,
          },
          "Femmes": {
            color: "rgba( 255, 128, 128, 1 )",
            strokeWidth: 2,
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
            <?php if ($log) echo "logscale: true,";  ?>
            drawGrid: true,
            gridLineColor: "rgba( 128, 128, 128, 0.5)",
            gridLineWidth: 1,
          },
          y2: {
            independentTicks: true,
            drawGrid: true,
            gridLinePattern: [6,3],
            gridLineColor: "rgba( 0, 0, 0, 0.2)",
            gridLineWidth: 1,
          },
        }
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
