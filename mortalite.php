<?php
$from = 1760;
$to = 1960;
include ( dirname(__FILE__).'/Cataviz.php' );
$db = new Cataviz( "databnf.sqlite" );

?><!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <title>Mortalité, Databnf.</title>
    <script src="lib/dygraph.min.js">//</script>
    <link rel="stylesheet" type="text/css" href="lib/dygraph.css"/>
    <link rel="stylesheet" type="text/css" href="cataviz.css"/>
    <style>
    .dygraph-legend { left: 7% !important; top: 0.5em !important; }
    .dygraph-axis-label-y2 { color: rgba( 192, 128, 192, 1 ); }
    .dygraph-y2label { color: rgba( 192, 128, 192, 1 ); }
    .ann { transform: rotateZ(-45deg); transform-origin: 10% 50%; padding-left: 1em; border-left: none !important; border-bottom: 1px solid #000 !important; font-size: 14pt !important; font-weight: normal; }
    </style>
  </head>
  <body>
    <?php include ( dirname(__FILE__).'/menu.php' ) ?>
    <header>
      <div class="links">
        <a href="" target="_new">Auteurs français, mortalité et longévité</a> 
        | <a href="?from=1500&amp;to=2015">5 siècles</a>
        | <a href="?from=1760&amp;to=1860">Révolutions</a>
        | <a href="?from=1860&amp;to=2020&amp;log=1">XX<sup>e</sup></a>
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

// pas de moyenne pour ces dates
$guerres = [ 1788, 1789, 1790, 1791, 1792, 1793, 1794,  1869, 1870, 1871, 1872,1913, 1914, 1915, 1916, 1917, 1918, 1919, 1939, 1940, 1941, 1942, 1943, 1944, 1945, 1946 ];
$guerres = array_flip( $guerres );

$qfage = $db->prepare( "SELECT avg(age) FROM person WHERE fr = 1 AND gender = 2 AND deathyear >= ? AND deathyear <= ? " );
// $qfcount = $db->prepare( "SELECT count(*) FROM person WHERE fr = 1 AND gender = 2 AND deathyear >= ? AND deathyear <= ? " );

$qmage = $db->prepare( "SELECT avg(age) FROM person WHERE fr = 1 AND gender = 1 AND deathyear >= ? AND deathyear <= ? " );
// $qmcount = $db->prepare( "SELECT count(*) FROM person WHERE fr = 1 AND gender = 1 AND deathyear >= ? AND deathyear <= ? " );

$qcount = $db->prepare( "SELECT count(*) FROM person WHERE fr = 1 AND deathyear >= ? AND deathyear <= ? " );

for ( $date=$from; $date <= $to; $date++ ) {

  $delta = 10;
  if ( $date > 1600 ) $delta = 5;
  if ( $date > 1700 ) $delta = 4;
  if ( $date > 1789 ) $delta = 3;
  if ( $date > 1900 ) $delta = 2;
  if ( isset( $guerres[$date] ) ) $delta = 0;
  echo "// $delta \n";

  $qcount->execute( array( $date-$delta, $date+$delta ) );
  list( $count ) = $qcount->fetch( PDO::FETCH_NUM );
  $count = $count/(1+2*$delta);

  $qmage->execute( array( $date-$delta, $date+$delta ) );
  list( $mage ) = $qmage->fetch( PDO::FETCH_NUM );

  $delta = floor( 3 * $delta );
  $qfage->execute( array( $date-$delta, $date+$delta ) );
  list( $fage ) = $qfage->fetch( PDO::FETCH_NUM );


  echo "[".$date;
  if ( !$fage ) echo ",";
  else echo ",". number_format( $fage, 2, '.', '');
  echo ",". number_format( $mage, 2, '.', '');
  echo ",". number_format( $count, 2, '.', '');
  echo "],\n";
}
       ?>],
      {
        labels: [ "Année", "♀ longévité", "♂ longévité", "Morts" ],
        legend: "always",
        labelsSeparateLines: "true",
        ylabel: "Âge à la mort",
        y2label: "Nombre de morts",
        showRoller: true,
        rollPeriod: <?php echo $smooth ?>,
        <?php if ($log) echo "logscale: true,";  ?>
        series: {
          "♀ longévité": {
            color: "rgba( 255, 128, 128, 0.7 )",
            strokeWidth: 4,
          },
          "♂ longévité": {
            color: "rgba( 0, 0, 192, 0.7 )",
            strokeWidth: 4,
          },
          "Morts": {
            axis: 'y2',
            color: "rgba( 128, 128, 128, 1 )",
            fillGraph: true,
            strokeWidth: 2,
          },
          "♂ morts": {
            axis: 'y2',
            color: "rgba( 0, 0, 192, 1 )",
            fillGraph: true,
            strokeWidth: 1,
          },
          "♀ morts": {
            axis: 'y2',
            color: "rgba( 255, 128, 128, 1 )",
            strokeWidth: 1,
            fillGraph: true,
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
          canvas.fillStyle = "rgba(255, 128, 0, 0.1)";
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
    g.ready(function() {
      g.setAnnotations([
        { series: "Morts", x: "1648", shortText: "La Fronde", width: "", height: "", cssClass: "annv", },
        { series: "Morts", x: "1789", shortText: "1789", width: "", height: "", cssClass: "annv", },
        { series: "Morts", x: "1815", shortText: "1815", width: "", height: "", cssClass: "annv", },
        { series: "Morts", x: "1830", shortText: "1830", width: "", height: "", cssClass: "annv", },
        { series: "Morts", x: "1848", shortText: "1848", width: "", height: "", cssClass: "annv", },
        { series: "Morts", x: "1870", shortText: "1870", width: "", height: "", cssClass: "annv", },
        { series: "Morts", x: "1914", shortText: "1914", width: "", height: "", cssClass: "annv", },
        { series: "Morts", x: "1939", shortText: "1939", width: "", height: "", cssClass: "annv", },
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
    <div class="text">
    <p>Pour chaque année, ce graphique projette les auteurs francophones d’un livre (plus de 50 pages) à leur date de mort, avec leur âge à la mort. </p>
    </div>
    <?php include ( dirname(__FILE__).'/footer.php' ) ?>
  </body>
</html>
