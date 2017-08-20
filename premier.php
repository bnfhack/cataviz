<?php
$from = 1900;
$to = 1960;
$datemax = 2008;
include ( dirname(__FILE__).'/Cataviz.php' );
$db = new Cataviz( "databnf.sqlite" );
// pour indice 100, même delta pour toutes les lignes ?
$base100 = floor (( $from + ($to - $from)/2.0 ) / 10.0) * 10;
if ( isset( $_REQUEST['base100'] ) && $_REQUEST['base100'] >= $from && $_REQUEST['base100'] <= $to ) $base100 = $_REQUEST['base100'];

?><!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <title>Âge à la publication, Databnf</title>
    <script src="lib/dygraph.min.js">//</script>
    <link rel="stylesheet" type="text/css" href="lib/dygraph.css"/>
    <link rel="stylesheet" type="text/css" href="cataviz.css"/>
    <style>
    .dygraph-legend { left: 8% !important; top: 40px !important;  }
    </style>
  </head>
  <body>
    <?php include ( dirname(__FILE__).'/menu.php' ) ?>
    <header>
      <div class="links">
        <a href="?">Âge à la publication</a> :
        <a href="?from=1600&amp;to=1788">1600–1789</a>,
        <a href="?from=1765&amp;to=1865">Révolution</a>,
        <a href="?from=1910&amp;to=2015&amp;log=true">XX<sup>e</sup></a>.
      </div>
      <form name="dates">
        <button onclick="window.location.href='?'; " type="button">Reset</button>
        De <input name="from" size="4" value="<?php echo $from ?>"/>
        à <input name="to" size="4" value="<?php echo  $to ?>"/>
        Base 100 en <input name="base100" size="4" value="<?php echo $base100 ?>"/>
        Échelle
        <button id="log" <?php if( $log ) echo'disabled="true"';?> type="button">log</button>
        <button id="linear" <?php if( !$log ) echo'disabled="true"';?> type="button">linéaire</button>
        <button type="submit">▶</button>
      </form>
    </header>
    <div id="chart" class="dygraph"></div>
    <script type="text/javascript">
    g = new Dygraph(
      document.getElementById("chart"),
      [
<?php

$agefq  = $db->prepare( "SELECT avg( age ) FROM document WHERE lang = 'fre' AND book = 1 AND gender=2 AND date >= ? AND date <= ?" );
$firstfq = $db->prepare( "SELECT avg( age1 ) FROM person WHERE fr = 1 AND gender=2 AND opus1 >= ? AND opus1 <= ? " );
$countfq = $db->prepare( "SELECT count(*) AS count FROM document WHERE lang='fre' AND book=1 AND posthum = 0 AND gender = 2 AND document.date >= ? AND document.date <= ? " );

$agemq  = $db->prepare( "SELECT avg( age ) FROM document WHERE lang = 'fre' AND book = 1 AND gender=1 AND date >= ? AND date <= ?" );
$firstmq = $db->prepare( "SELECT avg( age1 ) FROM person WHERE fr = 1 AND gender=1 AND opus1 >= ? AND opus1 <= ? " );
$countmq = $db->prepare( "SELECT count(*) AS count FROM document WHERE lang='fre' AND book=1 AND posthum = 0 AND gender = 1 AND document.date >= ? AND document.date <= ? " );


$delta100 = 3;
if ( $from >= 1700 ) $delta100 = 1;
if ( $from >= 1800 ) $delta100 = 0;
if ( $from >= 1900 ) $delta100 = 0;

$countfq->execute( array( $base100-$delta100, $base100+$delta100 ) );
list( $countf100 ) = $countfq->fetch( PDO::FETCH_NUM );
$countmq->execute( array( $base100-$delta100, $base100+$delta100 ) );
list( $countm100 ) = $countmq->fetch( PDO::FETCH_NUM );



for ( $date=$from; $date <= $to; $date++ ) {

  /*
  $ageq->execute( array( $date ) );
  list( $age ) = $ageq->fetch( PDO::FETCH_NUM );
  */

  $deltamod = 20;
  if ( $date >= 1700 ) $deltamod = 20;
  if ( $date >= 1800 ) $deltamod = 10;
  if ( $date >= 1900 ) $deltamod = 3;

  $delta = floor( 0.3*$deltamod );
  $agefq->execute( array( $date-$delta, $date+$delta ) );
  list( $agef ) = $agefq->fetch( PDO::FETCH_NUM );

  $delta = floor( 0.6*$deltamod );
  $firstfq->execute( array( $date-$delta, $date+$delta ) );
  list( $firstf ) = $firstfq->fetch( PDO::FETCH_NUM );

  $delta = floor( 0.1*$deltamod );
  $agemq->execute( array( $date-$delta, $date+$delta ) );
  list( $agem ) = $agemq->fetch( PDO::FETCH_NUM );
  $delta = floor( 0.2*$deltamod );
  $firstmq->execute( array( $date-$delta, $date+$delta ) );
  list( $firstm ) = $firstmq->fetch( PDO::FETCH_NUM );

  $countfq->execute( array( $date-$delta100, $date+$delta100 ) );
  list( $countf ) = $countfq->fetch( PDO::FETCH_NUM );
  $countmq->execute( array( $date-$delta100, $date+$delta100 ) );
  list( $countm ) = $countmq->fetch( PDO::FETCH_NUM );


  echo "[".$date;

  if ( !$agef ) echo ',';
  else echo ",".number_format( $agef, 2, '.', '' );
  echo ",".number_format( $firstf, 2, '.', '' );
  echo ",". number_format( 100.0* $countf  / $countf100, 2, '.', '');

  if ( !$agem ) echo ',';
  else echo ",".number_format( $agem, 2, '.', '' );
  echo ",".number_format( $firstm, 2, '.', '' );
  echo ",". number_format( 100.0* $countm  / $countm100, 2, '.', '');



  echo "],\n";

}
       ?>],
      {
        title : "Databnf, âges moyens à la date de publication (livres, base 100 en <?=$base100?>).",
        titleHeight: 35,
        labels: [ "Année", "♀ Âge à la publication", "♀ Âge au premier livre", "♀ Livres", "♂ Âge à la publication", "♂ Âge au premier livre", "♂ Livres" ],
        legend: "always",
        labelsSeparateLines: "true",
        ylabel: "Âge moyen",
        y2label: "Base 100 en <?=$base100?>",
        showRoller: true,
        rollPeriod: <?php echo $smooth ?>,
        <?php if ($log) echo "logscale: 'true',\n";  ?>
        series: {
          "♀ Âge à la publication": {
            axis: 'y',
            color: "rgba( 255, 128, 128, 0.5 )",
            strokeWidth: 2,
            fillGraph: true,
          },
          "♀ Âge au premier livre": {
            axis: 'y',
            color: "rgba( 255, 128, 128, 1 )",
            fillGraph: true,
            strokeWidth: 0.5,
          },
          "♀ Livres": {
            axis: 'y2',
            color: "rgba( 255, 0, 0, 0.7 )",
            strokeWidth: 3,
            strokePattern: [5,3],
          },
          "♂ Âge à la publication": {
            axis: 'y',
            color: "rgba( 128, 128, 192, 0.5 )",
            strokeWidth: 2,
            fillGraph: true,
          },
          "♂ Âge au premier livre": {
            axis: 'y',
            color: "rgba( 128, 128, 192, 1 )",
            strokeWidth: 1.5,
            fillGraph: true,
          },
          "♂ Livres": {
            axis: 'y2',
            color: "rgba( 0, 0, 128, 0.7 )",
            strokeWidth: 3,
            strokePattern: [5,3],
          },
        },
        axes: {
          x: {
            gridLineWidth: 1,
            drawGrid: true,
            gridLineColor: "rgba( 128, 128, 128, 0.3)",
            independentTicks: true,
          },
          y: {
            independentTicks: true,
            drawGrid: true,
            gridLineColor: "rgba( 128, 128, 128, 0.3)",
            gridLineWidth: 1,
            // gridLinePattern: [4,3],
          },
          y2: {
            independentTicks: true,
            drawGrid: false,
            gridLineColor: "rgba( 192, 128, 160, 0.5)",
            gridLineWidth: 1,
          },
        },
        underlayCallback: function(canvas, area, g) {
          canvas.fillStyle = "rgba(192, 192, 192, 0.3)";
          var periods = [ [1789,1794], [1814,1815], [1830,1831], [1848,1849], [1870,1871], [1914,1919], [1939,1945]];
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
        { series: "♂ Âge au premier livre", x: "1648", shortText: "La Fronde", width: "", height: "", cssClass: "annv", },
        { series: "♂ Âge au premier livre", x: "1789", shortText: "1789", width: "", height: "", cssClass: "annv", },
        { series: "♂ Âge au premier livre", x: "1815", shortText: "1815", width: "", height: "", cssClass: "annv", },
        { series: "♂ Âge au premier livre", x: "1830", shortText: "1830", width: "", height: "", cssClass: "annv", },
        { series: "♂ Âge au premier livre", x: "1848", shortText: "1848", width: "", height: "", cssClass: "annv", },
        { series: "♂ Âge au premier livre", x: "1870", shortText: "1870", width: "", height: "", cssClass: "annv", },
        { series: "♂ Âge au premier livre", x: "1914", shortText: "1914", width: "", height: "", cssClass: "annv", },
        { series: "♂ Âge au premier livre", x: "1939", shortText: "1939", width: "", height: "", cssClass: "annv", },
      ]);
    });
    var linear = document.getElementById("linear");
    var log = document.getElementById("log");
    if ( linear && log ) {
      var setLog = function(val) {
        g.updateOptions({ logscale: val });
        linear.disabled = !val;
        log.disabled = val;
      };
      linear.onclick = function() { setLog(false); };
      log.onclick = function() { setLog(true); };
    }
    </script>
    <p>Ce graphique agrège des informations pour comprendre l’âge moyen à la publication d’un livre. Les surfaces indiquent l les femmes ; plutôt bleu les hommes ; en violet, âge moyen du premier livre des hommes et des femmes. Les courbes sont ramenées à un indice 100 </p>
    <?php include ( dirname(__FILE__).'/footer.php' ) ?>
  </body>
</html>
