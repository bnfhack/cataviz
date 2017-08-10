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
else $smooth = 4;
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
$qmparis = $db->prepare( "SELECT count(*) AS count FROM person WHERE fr = 1 AND deathyear = ? AND deathparis = 1 AND gender = 1 " );
$qmailleurs = $db->prepare( "SELECT count(*) AS count FROM person WHERE fr = 1 AND deathyear = ?  AND deathparis = 0 AND gender = 1 " );
$qfparis = $db->prepare( "SELECT count(*) AS count FROM person WHERE fr = 1 AND deathyear >= ? AND deathyear <= ? AND deathparis = 1 AND gender = 2 " );
$qfailleurs = $db->prepare( "SELECT count(*) AS count FROM person WHERE fr = 1 AND deathyear >= ? AND deathyear <= ? AND deathparis = 0 AND gender = 2 " );


for ( $date=$from; $date <= $to; $date++ ) {
  $qmparis->execute( array( $date ) );
  list( $mparis ) = $qmparis->fetch( PDO::FETCH_NUM );
  $qmailleurs->execute( array( $date ) );
  list( $mailleurs ) = $qmailleurs->fetch( PDO::FETCH_NUM );
  $delta = 15;
  $qfparis->execute( array( $date - $delta, $date + $delta ) );
  list( $fparis ) = $qfparis->fetch( PDO::FETCH_NUM );
  $qfailleurs->execute( array( $date - $delta, $date + $delta ) );
  list( $failleurs ) = $qfailleurs->fetch( PDO::FETCH_NUM );

  echo "[".$date;
  echo ",". number_format( 100.0 * $mparis / ($mparis+$mailleurs), 2, '.', '');
  if ( $fparis+$failleurs == 0 ) echo ", 0";
  else echo ",". number_format( 100.0 * $fparis / ($fparis+$failleurs), 2, '.', '');
  echo "],\n";
}
       ?>],
      {
        labels: [ "Année", "Hommes", "Femmes" ],
        legend: "always",
        labelsSeparateLines: "true",
        ylabel: "% auteurs morts à Paris",
        y2label: "Âge à la mort",
        showRoller: true,
        rollPeriod: <?php echo $smooth ?>,
        series: {
          "Hommes": {
            color: "rgba( 0, 0, 192, 1 )",
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
    g.ready(function() {
      g.setAnnotations([
        { series: "Hommes", x: "1648", shortText: "La Fronde", width: "", height: "", cssClass: "ann", },
        { series: "Hommes", x: "1793", shortText: "1793", width: "", height: "", cssClass: "ann", },
        { series: "Hommes", x: "1870", shortText: "1870", width: "", height: "", cssClass: "ann", },
        { series: "Hommes", x: "1914", shortText: "1914", width: "", height: "", cssClass: "ann", },
        { series: "Hommes", x: "1939", shortText: "1939", width: "", height: "", cssClass: "ann", },
      ]);
    });
    var linear = document.getElementById("linear");
    var log = document.getElementById("log");
    if ( log && linear ) {
      var setLog = function(val) {
        g.updateOptions({ logscale: val });
        linear.disabled = !val;
        log.disabled = val;
      };
      linear.onclick = function() { setLog(false); };
      log.onclick = function() { setLog(true); };
    }
    </script>
    <p>Population d’auteurs </p>
    <?php include ( dirname(__FILE__).'/footer.php' ) ?>
  </body>
</html>
