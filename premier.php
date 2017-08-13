<?php
$from = 1900;
$to = 1960;
include ( dirname(__FILE__).'/Cataviz.php' );
$db = new Cataviz( "databnf.sqlite" );

?><!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <title>Âge à la publication, Databnf</title>
    <script src="lib/dygraph.min.js">//</script>
    <link rel="stylesheet" type="text/css" href="lib/dygraph.css"/>
    <link rel="stylesheet" type="text/css" href="cataviz.css"/>
    <style>
    .dygraph-legend { left: 20% !important; top: 0.5em !important; }
    .dygraph-ylabel { color: rgba( 0, 0, 0, 0.7 ); font-weight: normal; }
    .dygraph-axis-label-y2 { color: rgba( 192, 128, 160, 0.9 ); }
    .dygraph-y2label { color: rgba( 192, 128, 160, 0.6 ); }
    .ann { transform: rotateZ(-90deg); transform-origin: 0% 100%; padding-left: 1em; border-left: none !important; border-bottom: 1px solid #000 !important; font-size: 16pt !important; font-weight: bold; color: rgba( 0, 0, 0, 0.8) !important; }
    </style>
  </head>
  <body>
    <?php include ( dirname(__FILE__).'/menu.php' ) ?>
    <header>
      <div class="links">
        <a href="?">Âge à la publication</a> :
        <a href="?from=1600&amp;to=1788&amp;smooth=8">1600–1789</a>,
        <a href="?from=1765&amp;to=1865">Révolution</a>,
        <a href="?from=1910&amp;to=2015">XX<sup>e</sup></a>.
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
    <div id="chart" class="dygraph"></div>
    <script type="text/javascript">
    g = new Dygraph(
      document.getElementById("chart"),
      [
<?php

$agefq  = $db->prepare( "SELECT avg( age ) FROM document WHERE lang = 'fre' AND book = 1 AND gender=2 AND date >= ? AND date <= ?" );
$firstfq = $db->prepare( "SELECT avg( age1 ) FROM person WHERE fr = 1 AND gender=2 AND opus1 >= ? AND opus1 <= ? " );

$agemq  = $db->prepare( "SELECT avg( age ) FROM document WHERE lang = 'fre' AND book = 1 AND gender=1 AND date >= ? AND date <= ?" );
$firstmq = $db->prepare( "SELECT avg( age1 ) FROM person WHERE fr = 1 AND gender=1 AND opus1 >= ? AND opus1 <= ? " );

// $ageq  = $db->prepare( "SELECT avg( age ) FROM document WHERE lang = 'fre' AND book = 1 AND date = ?" );
/*
$hq = $db->prepare( "SELECT avg( opus1 - birthyear ) FROM person WHERE fr = 1 AND opus1 >= ? AND opus1 <= ? AND gender = 1 " );
$fq = $db->prepare( "SELECT avg( opus1 - birthyear ) FROM person WHERE fr = 1 AND opus1 >= ? AND opus1 <= ? AND gender = 2 " );
*/

$totq = $db->prepare( "SELECT count(*) AS count FROM document WHERE lang = 'fre'  AND book = 1 AND date >= ? AND date <= ? " );
$antq = $db->prepare( "SELECT count(*) AS count FROM document WHERE posthum = 0 AND book = 1 AND lang = 'fre' AND date >= ? AND date <= ? " );
$postq = $db->prepare( "SELECT count(*) AS count FROM document WHERE posthum = 1 AND book = 1 AND lang = 'fre' AND date >= ? AND date <= ?" );
$premq = $db->prepare( "SELECT count(*) AS count FROM person WHERE fr = 1 AND opus1 >= ? AND opus1 <= ? " );




// pour indice 100, même delta pour toutes les lignes ?
$med = floor (( $from + ($to - $from)/2.0 ) / 10.0) * 10;

$deltalines = 3;
if ( $from >= 1700 ) $deltalines = 2;
if ( $from >= 1800 ) $deltalines = 1;
if ( $from >= 1900 ) $deltalines = 0;

$totq->execute( array( $med-$deltalines, $med+$deltalines ) );
list( $tot100 ) = $totq->fetch( PDO::FETCH_NUM );
$antq->execute( array( $med-$deltalines, $med+$deltalines ) );
list( $ant100 ) = $antq->fetch( PDO::FETCH_NUM );
$postq->execute( array( $med, $med ) );
list( $post100 ) = $postq->fetch( PDO::FETCH_NUM );
$premq->execute( array( $med-($deltalines*2), $med+($deltalines*2) ) );
list( $prem100 ) = $premq->fetch( PDO::FETCH_NUM );


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



  $totq->execute( array( $date-$deltalines, $date+$deltalines ) );
  list( $totcount ) = $totq->fetch( PDO::FETCH_NUM );

  $antq->execute( array( $date-$deltalines, $date+$deltalines ) );
  list( $antcount ) = $antq->fetch( PDO::FETCH_NUM );

  $postq->execute( array( $date, $date ) );
  list( $postcount ) = $postq->fetch( PDO::FETCH_NUM );

  $premq->execute( array( $date-($deltalines*2), $date+($deltalines*2) ) );
  list( $premcount ) = $premq->fetch( PDO::FETCH_NUM );


  echo "[".$date;

  if ( !$agef ) echo ',';
  else echo ",".number_format( $agef, 2, '.', '' );
  echo ",".number_format( $firstf, 2, '.', '' );
  if ( !$agem ) echo ',';
  else echo ",".number_format( $agem, 2, '.', '' );
  echo ",".number_format( $firstm, 2, '.', '' );


  echo ",". number_format( 100.0* $totcount  / $tot100, 2, '.', '');
  echo ",". number_format( 100.0* $postcount  / $post100, 2, '.', '');
  echo ",". number_format( 100.0* $premcount  / $prem100, 2, '.', '');

  echo "],\n";

}
       ?>],
      {
        labels: [ "Année", "♀ Âge à la publication", "♀ Âge au premier livre", "♂ Âge à la publication", "♂ Âge au premier livre", "Livres", "Rééditions", "Premiers livres" ],
        legend: "always",
        labelsSeparateLines: "true",
        y2label: "Âge moyen",
        ylabel: "Indice 100 en <?=$med?>",
        showRoller: true,
        rollPeriod: <?php echo $smooth ?>,
        <?php if ($log) echo "logscale: 'true',\n";  ?>
        series: {
          "♀ Âge à la publication": {
            axis: 'y2',
            color: "rgba( 255, 192, 192, 1 )",
            strokeWidth: 1,
            fillGraph: true,
          },
          "♀ Âge au premier livre": {
            axis: 'y2',
            color: "rgba( 255, 128, 128, 1 )",
            strokeWidth: 2,
          },
          "♂ Âge à la publication": {
            axis: 'y2',
            color: "rgba( 128, 128, 192, 1 )",
            strokeWidth: 1,
            fillGraph: true,
          },
          "♂ Âge au premier livre": {
            axis: 'y2',
            color: "rgba( 0, 0, 128, 1 )",
            strokeWidth: 2,
          },
          "Âge à la publication" : {
            axis: 'y2',
            color: "rgba( 192, 192, 192, 1)",
            strokeWidth: 1,
            fillGraph: true,
          },
          "♂ âge au premier livre": {
            axis: 'y2',
            color: "rgba( 0, 0, 128, 0.5 )",
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
            strokeWidth: 3,
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
            drawGrid: false,
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
        { series: "Âge au premier livre", x: "1648", shortText: "La Fronde", width: "", height: "", cssClass: "ann", },
        { series: "Âge au premier livre", x: "1789", shortText: "1789", width: "", height: "", cssClass: "ann", },
        { series: "Âge au premier livre", x: "1815", shortText: "1815", width: "", height: "", cssClass: "ann", },
        { series: "Âge au premier livre", x: "1830", shortText: "1830", width: "", height: "", cssClass: "ann", },
        { series: "Âge au premier livre", x: "1848", shortText: "1848", width: "", height: "", cssClass: "ann", },
        { series: "Âge au premier livre", x: "1870", shortText: "1870", width: "", height: "", cssClass: "ann", },
        { series: "Âge au premier livre", x: "1914", shortText: "1914", width: "", height: "", cssClass: "ann", },
        { series: "Âge au premier livre", x: "1939", shortText: "1939", width: "", height: "", cssClass: "ann", },
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
