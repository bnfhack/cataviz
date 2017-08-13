<?php
$smooth = 5;
include ( dirname(__FILE__).'/Cataviz.php' );
$db = new Cataviz( "databnf.sqlite" );

?><!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <script src="lib/dygraph.min.js">//</script>
    <link rel="stylesheet" type="text/css" href="lib/dygraph.css"/>
    <link rel="stylesheet" type="text/css" href="cataviz.css"/>
    <style>
    .dygraph-legend { left: 40% !important; top: 0.5em !important; }
    /*
    .ann { transform: rotateZ(45deg); transform-origin: 10% 50%; padding-left: 1em; border-left: none !important; border-bottom: 1px solid #000 !important; font-size: 14pt !important; font-weight: normal; }
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
    <div id="chart" class="dygraph"></div>
    <script type="text/javascript">
    g = new Dygraph(
      document.getElementById("chart"),
      [
<?php
$qmparis = $db->prepare( "SELECT count(*) AS count FROM person WHERE fr = 1 AND opus1 >= ? AND opus1 <= ? AND birthparis = 1 AND gender = 1  " );
$qmailleurs = $db->prepare( "SELECT count(*) AS count FROM person WHERE fr = 1 AND opus1 >= ? AND opus1 <= ? AND birthparis = 0 AND gender = 1 " );
$qfparis = $db->prepare( "SELECT count(*) AS count FROM person WHERE fr = 1 AND opus1 >= ? AND opus1 <= ? AND birthparis = 1 AND gender = 2 " );
$qfailleurs = $db->prepare( "SELECT count(*) AS count FROM person WHERE fr = 1 AND opus1 >= ? AND opus1 <= ? AND birthparis = 0 AND gender = 2 " );

// proportion grands auteurs similaire aux autres
// $q5paris = $db->prepare( "SELECT count(*) AS count FROM person WHERE fr = 1 AND birthyear >= ? AND birthyear <= ? AND birthparis = 1 AND anthum > 20 " );
// $q5ailleurs = $db->prepare( "SELECT count(*) AS count FROM person WHERE fr = 1 AND birthyear >= ? AND birthyear <= ? AND birthparis = 0 AND anthum > 20 " );

$last = 1;
$all = 1;
for ( $date=$from; $date <= $to; $date++ ) {
  $delta = 0;
  $qmparis->execute( array( $date - $delta, $date + $delta ) );
  list( $mparis ) = $qmparis->fetch( PDO::FETCH_NUM );
  $qmailleurs->execute( array( $date - $delta, $date + $delta ) );
  list( $mailleurs ) = $qmailleurs->fetch( PDO::FETCH_NUM );
  $delta = 5;
  $qfparis->execute( array( $date - $delta, $date + $delta ) );
  list( $fparis ) = $qfparis->fetch( PDO::FETCH_NUM );
  $qfailleurs->execute( array( $date - $delta, $date + $delta ) );
  list( $failleurs ) = $qfailleurs->fetch( PDO::FETCH_NUM );


  echo "[".$date;
  echo ",". number_format( 100.0 * $mparis / ($mparis+$mailleurs), 2, '.', '');
  if ( $fparis+$failleurs == 0 ) echo ", 0";
  else echo ",". number_format( 100.0 * $fparis / ($fparis+$failleurs), 2, '.', '');
  echo "],\n";
  $last = $all;
}
       ?>],
      {
        labels: [ "Année", "Hommes", "Femmes" ],
        legend: "always",
        labelsSeparateLines: "true",
        ylabel: "% auteurs nés à Paris",
        y2label: "Âge à la mort",
        showRoller: true,
        rollPeriod: <?php echo $smooth ?>,
        <?php if ($log) echo "logscale: true,";  ?>
        series: {
          "Hommes": {
            color: "rgba( 0, 0, 192, 1 )",
            strokeWidth: 2,
          },
          "Femmes": {
            color: "rgba( 255, 128, 128, 1 )",
            strokeWidth: 2,
          },
          "% var. naissances": {
            axis: 'y2',
            color: "rgba( 128, 128, 128, 0.5 )",
            strokeWidth: 4,
          },
        },
        axes: {
          x: {
            gridLineWidth: 1,
            drawGrid: true,
            independentTicks: true,
            gridLineColor: "rgba( 128, 128, 128, 0.5)",
          },
          y: {
            independentTicks: true,
            drawGrid: true,
            gridLineColor: "rgba( 128, 128, 128, 0.1)",
            gridLineWidth: 1,
          },
          y2: {
            independentTicks: true,
            drawGrid: true,
            // gridLinePattern: [6,3],
            gridLineColor: "rgba( 128, 128, 128, 0.2)",
            gridLineWidth: 3,
          },
        }
      }
    );
    g.ready(function() {
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
