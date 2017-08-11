<?php
// header('Content-type: text/plain; charset=utf-8');
include ( dirname(__FILE__).'/Cataviz.php' );
$db = new Cataviz( "databnf.sqlite" );
$datemax = 2015;
if (isset($_REQUEST['from'])) $from = $_REQUEST['from'];
else $from = 1760;
if ( $from < 1452 ) $from = 1452;
if ( $from > $datemax ) $from = $datemax;
if (isset($_REQUEST['to'])) $to = $_REQUEST['to'];
else $to = 1960;
if ( $to < 1475 ) $to = $datemax;
if ( $to > $datemax ) $to = $datemax;

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
    .dygraph-legend { left: 20% !important; top: 0.5em !important; }
    .dygraph-ylabel { color: rgba( 0, 0, 0, 0.7 ); font-weight: normal; }
    .dygraph-axis-label-y2 { color: rgba( 192, 128, 160, 0.9 ); }
    .dygraph-y2label { color: rgba( 192, 128, 160, 0.6 ); }
    .ann { transform: rotateZ(-45deg); transform-origin: 10% 50%; padding-left: 1em; border-left: none !important; border-bottom: 1px solid #000 !important; font-size: 14pt !important; font-weight: normal; }
    </style>
  </head>
  <body>
    <?php include ( dirname(__FILE__).'/menu.php' ) ?>
    <header>
      <div class="links">
        <a href="" target="_new">Data.bnf.fr, premiers livres</a> 
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
    <div id="chart" class="dygraph" style="width:100%; height:500px;"></div>
    <script type="text/javascript">
    g = new Dygraph(
      document.getElementById("chart"),
      [
<?php

$fromgender = 1814;

// $agehq  = $db->prepare( "SELECT avg( date - birthyear ) FROM document WHERE lang = 'fre' AND book = 1 AND posthum=0 AND gender=1 AND date >= ? AND date <= ?" );
// $agefq  = $db->prepare( "SELECT avg( date - birthyear ) FROM document WHERE lang = 'fre' AND book = 1 AND posthum=0 AND gender=2 AND date >= ? AND date <= ?" );

$ageq  = $db->prepare( "SELECT avg( date - birthyear ) FROM document WHERE lang = 'fre' AND book = 1 AND posthum=0 AND date = ?" );
/*
$hq = $db->prepare( "SELECT avg( opus1 - birthyear ) FROM person WHERE fr = 1 AND opus1 >= ? AND opus1 <= ? AND gender = 1 " );
$fq = $db->prepare( "SELECT avg( opus1 - birthyear ) FROM person WHERE fr = 1 AND opus1 >= ? AND opus1 <= ? AND gender = 2 " );
*/

$totq = $db->prepare( "SELECT count(*) AS count FROM document WHERE lang = 'fre'  AND book = 1 AND date = ? " );
$antq = $db->prepare( "SELECT count(*) AS count FROM document WHERE posthum = 0 AND book = 1 AND lang = 'fre' AND date = ? " );
$postq = $db->prepare( "SELECT count(*) AS count FROM document WHERE posthum = 1 AND book = 1 AND lang = 'fre' AND date = ?" );
$premq = $db->prepare( "SELECT avg( opus1 - birthyear ), count(*) AS count FROM person WHERE fr = 1 AND opus1 >= ? AND opus1 <= ? " );

// $delta, modulo hauteur et largeur de la courbe
$deltaq = $db->prepare( "SELECT count(*) AS count FROM document WHERE type = 'Text' AND lang = 'fre' AND book = 1 AND date = ?" );
$deltaq->execute( array( $from ) );
list( $val ) = $deltaq->fetch( PDO::FETCH_NUM );
$deltamod = sqrt( ( $to - $from ) / sqrt( $val ) );


for ( $date=$from; $date <= $to; $date++ ) {

  $delta = floor( 1.5*$deltamod );
  // $ageq->execute( array( $date-$delta, $date+$delta ) );
  $ageq->execute( array( $date ) );
  list( $age ) = $ageq->fetch( PDO::FETCH_NUM );

  /*
  $delta = floor( 0.5*$deltamod );
  $agehq->execute( array( $date-$delta, $date+$delta ) );
  list( $ageh ) = $agehq->fetch( PDO::FETCH_NUM );

  $delta = floor( 1.5*$deltamod );
  $agefq->execute( array( $date-$delta, $date+$delta ) );
  list( $agef ) = $agefq->fetch( PDO::FETCH_NUM );
  */
  /*


  if ( $from >= $fromgender ) {
    $delta = floor( $deltamod );
    $hq->execute( array( $date-$delta, $date+$delta ) );
    list( $hage ) = $hq->fetch( PDO::FETCH_NUM );
    $delta = floor( 2.0*$deltamod );
    $fq->execute( array( $date-$delta, $date+$delta ) );
    list( $fage ) = $fq->fetch( PDO::FETCH_NUM );
  }
  */

  $totq->execute( array( $date ) );
  list( $totcount ) = $totq->fetch( PDO::FETCH_NUM );
  if( !isset( $tot100 ) ) $tot100 = $totcount;

  $antq->execute( array( $date ) );
  list( $antcount ) = $antq->fetch( PDO::FETCH_NUM );
  if( !isset( $ant100 ) ) $ant100 = $antcount;

  $postq->execute( array( $date ) );
  list( $postcount ) = $postq->fetch( PDO::FETCH_NUM );
  if( !isset( $post100 ) ) $post100 = $postcount;

  $delta = floor( 1.5*$deltamod );
  $premq->execute( array( $date-$delta, $date+$delta ) );
  list( $premage, $premcount ) = $premq->fetch( PDO::FETCH_NUM );
  if( !isset( $prem100 ) ) $prem100 = $premcount;


  echo "[".$date;

  echo ",". number_format( $age, 2, '.', '');
  if ( true || $from < $fromgender ) {
    echo ",".number_format( $premage, 2, '.', '' );
  }
  // ? genrer ?
  else {
    if ( !$hage ) echo ',';
    else echo ",".number_format( $hage, 2, '.', '' );
    // pas de données
    if ( !$fage ) echo ',';
    else echo ",".number_format( $fage, 2, '.', '' );
  }

  echo ",". number_format( 100.0* $totcount  / $tot100, 2, '.', '');
  echo ",". number_format( 100.0* $postcount  / $post100, 2, '.', '');
  echo ",". number_format( 100.0* $premcount  / $prem100, 2, '.', '');

  echo "],\n";

}
       ?>],
      {
        labels: [ "Année", "Âge à la publication", <?php
         if ( true || $from < $fromgender ) echo '"Âge au premier livre", ';
         // else echo '"♂ âge au premier livre", "♀ âge au premier livre"';
         ?>"Livres", "Rééditions", "Premiers livres" ],
        legend: "always",
        labelsSeparateLines: "true",
        y2label: "Âge moyen",
        ylabel: "Indice 100 en <?=$from?>",
        showRoller: true,
        rollPeriod: <?php echo $smooth ?>,
        <?php if ($log) echo "logscale: 'true',\n";  ?>
        series: {
          "Âge à la publication" : {
            axis: 'y2',
            color: "rgba( 192, 192, 192, 1)",
            strokeWidth: 1,
            fillGraph: true,
          },
          "Âge au premier livre": {
            axis: 'y2',
            color: "rgba( 192, 128, 160, 0.5)",
            strokeWidth: 1,
            fillGraph: true,
          },
          "♂ Âge à la publication": {
            axis: 'y2',
            color: "rgba( 0, 0, 128, 0.7 )",
            strokeWidth: 1,
            fillGraph: true,
          },
          "♂ âge au premier livre": {
            axis: 'y2',
            color: "rgba( 0, 0, 128, 0.7 )",
            strokeWidth: 1,
            fillGraph: true,
          },
          "♀ Âge à la publication": {
            axis: 'y2',
            color: "rgba( 255, 128, 128, 0.7 )",
            strokeWidth: 1,
            fillGraph: true,
          },
          "♀ âge au premier livre": {
            axis: 'y2',
            color: "rgba( 255, 128, 128, 0.7 )",
            strokeWidth: 1,
            fillGraph: true,
          },
          "Livres": {
            axis: 'y',
            color: "rgba( 0, 0, 0, 1)",
            strokeWidth: 2,
          },
          "Nouveautés": {
            axis: 'y',
            color: "rgba( 160, 160, 160, 1)",
            strokeWidth: 4,
            strokePattern: [8,4],
            // fillGraph: true,
          },
          "Rééditions": {
            axis: 'y',
            color: "rgba( 128, 128, 128, 1)",
            strokeWidth: 4,
            strokePattern: [6,2],
          },
          "Premiers livres": {
            axis: 'y',
            color: "rgba( 160, 160, 160, 1)",
            strokeWidth: 4,
          },
        },
        axes: {
          x: {
            gridLineWidth: 2,
            drawGrid: true,
            gridLineColor: "rgba( 128, 128, 128, 0.3)",
            gridLineWidth: 1,
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
            drawGrid: true,
            gridLineColor: "rgba( 192, 128, 160, 0.5)",
            gridLineWidth: 1,
          },
        },
        underlayCallback: function(canvas, area, g) {
          canvas.fillStyle = "rgba(255, 128, 0, 0.2)";
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
      /*
      g.setAnnotations([
        { series: "Livres", x: "1648", shortText: "La Fronde", width: "", height: "", cssClass: "ann", },
        { series: "Livres", x: "1793", shortText: "1793", width: "", height: "", cssClass: "ann", },
        { series: "Livres", x: "1830", shortText: "1830", width: "", height: "", cssClass: "ann", },
        { series: "Livres", x: "1870", shortText: "1870", width: "", height: "", cssClass: "ann", },
        { series: "Livres", x: "1914", shortText: "1914", width: "", height: "", cssClass: "ann", },
        { series: "Livres", x: "1939", shortText: "1939", width: "", height: "", cssClass: "ann", },
      ]);
      */
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
