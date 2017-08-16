<?php
$from = 1800;
$to = 1960;
include ( dirname(__FILE__).'/Cataviz.php' );
$db = new Cataviz( "databnf.sqlite" );
$books = @$_REQUEST['books'];
if ( !$books ) $books = 10;

?><!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <script src="lib/dygraph.min.js">//</script>
    <link rel="stylesheet" type="text/css" href="lib/dygraph.css"/>
    <link rel="stylesheet" type="text/css" href="cataviz.css"/>
    <style>
    .dygraph-legend { left: auto !important; right: 0.5ex !important; width: 32ex; top: 40px !important; }
    /*
    .ann { transform: rotateZ(45deg); transform-origin: 10% 50%; padding-left: 1em; border-left: none !important; border-bottom: 1px solid #000 !important; font-size: 14pt !important; font-weight: normal; }
    */
    </style>
  </head>
  <body>
    <?php include ( dirname(__FILE__).'/menu.php' ) ?>
    <header>
      <div class="links">
        <a href="" target="_new">Lieux de naissance</a>
        | <a href="?from=1760&amp;to=1860">Révolution</a> 
        | <a href="?from=1900&amp;to=1960">Guerres mondiales</a> 
      </div>
      <form name="dates">
        <button onclick="window.location.href='?'; " type="button">Reset</button>
        De <input name="from" size="4" value="<?php echo $from ?>"/>
        à <input name="to" size="4" value="<?php echo  $to ?>"/>
        Seuil livres <input name="books" size="4" value="<?php echo $books ?>"/>
        <button type="submit">▶</button>
      </form>
    </header>
    <div id="chart" class="dygraph"></div>
    <script type="text/javascript">
    g = new Dygraph(
      document.getElementById("chart"),
      [
<?php

$qf = $db->prepare( "SELECT birthparis, COUNT(*) AS count FROM person WHERE fr = 1 AND gender = 2 AND opus1 >= ? AND opus1 <= ? GROUP BY birthparis ORDER BY birthparis" );
$qfbooks = $db->prepare( "SELECT birthparis, COUNT(*) AS count FROM person WHERE fr = 1 AND gender = 2 AND opus1 >= ? AND opus1 <= ? AND books > ? GROUP BY birthparis ORDER BY birthparis" );
$qm = $db->prepare( "SELECT birthparis, COUNT(*) AS count FROM person WHERE fr = 1 AND gender = 1 AND opus1 >= ? AND opus1 <= ? GROUP BY birthparis ORDER BY birthparis" );
$qmbooks = $db->prepare( "SELECT birthparis, COUNT(*) AS count FROM person WHERE fr = 1 AND gender = 1 AND opus1 >= ? AND opus1 <= ? AND books > ? GROUP BY birthparis ORDER BY birthparis" );

for ( $date=$from; $date <= $to; $date++ ) {
  echo "[".$date;

  $f=$m=$fbooks=$mbooks=array( null=>array(0), 0=>array(0), 1=>array(0) );

  $delta = 20;
  if ( $date > 1600 ) $delta = 15;
  // if ( $date > 1700 ) $delta = 12;
  if ( $date > 1780 ) $delta = 10;
  if ( $date >= 1900 ) $delta = 6;
  if ( isset( $guerres[$date] ) && $date > 1900 ) $delta = 2;

  $qf->execute( array( $date-$delta, $date+$delta ) );
  while ($row = $qf->fetch( PDO::FETCH_NUM )) {
    $f[$row[0]] = $row;
  }
  $qfbooks->execute( array( $date-$delta, $date+$delta, $books ) );
  while ($row = $qfbooks->fetch( PDO::FETCH_NUM )) {
    $fbooks[$row[0]] = $row;
  }

  echo ",". @number_format( 100.0 * $f[1][1] / ( $f[0][1]+$f[1][1] ), 2, '.', '');
  echo ",". @number_format( 100.0 * $fbooks[1][1] / ( $fbooks[0][1]+$fbooks[1][1] ), 2, '.', '');

  $delta = 7;
  if ( $date >= 1600 ) $delta = 3;
  if ( $date >= 1700 ) $delta = 3;
  if ( $date >= 1789 ) $delta = 2;
  $qm->execute( array( $date-$delta, $date+$delta ) );
  while ($row = $qm->fetch( PDO::FETCH_NUM )) {
    $m[$row[0]] = $row;
  }
  $qmbooks->execute( array( $date-$delta, $date+$delta, $books ) );
  while ($row = $qmbooks->fetch( PDO::FETCH_NUM )) {
    $mbooks[$row[0]] = $row;
  }
  echo ",". @number_format( 100.0 * $m[1][1] / ( $m[0][1]+$m[1][1] ), 2, '.', '');
  echo ",". @number_format( 100.0 * $mbooks[1][1] / ( $mbooks[0][1]+$mbooks[1][1] ), 2, '.', '');

  echo "],\n";
}
       ?>],
      {
        title : "Databnf, lieu de naissance (Paris/Ailleurs), à la date du premier livre.",
        titleHeight: 35,
        labels: [ "Année",
          "♀ % née à Paris", "♀ % née à Paris > <?=$books?> livres",
          "♂ % né à Paris", "♂ % né à Paris > <?=$books?> livres",
        ],
        legend: "always",
        labelsSeparateLines: "true",
        ylabel: "% auteurs nés à Paris",
        y2label: "Nombre moyen de livres",
        // showRoller: true,
        // rollPeriod: <?php echo $smooth ?>,
        <?php if ($log) echo "logscale: 'true',\n";  ?>
        series: {
          "♀ % née à Paris": {
            color: "rgba( 255, 160, 160, 1 )",
            strokeWidth: 1,
            fillGraph: true,
          },
          "♂ % né à Paris": {
            color: "rgba( 160, 160, 255, 1 )",
            strokeWidth: 1,
            fillGraph: true,
          },
          "♀ % née à Paris > <?=$books?> livres" : {
            color: "rgba( 255, 0, 0, 0.2 )",
            strokeWidth: 4,
          },
          "♂ % né à Paris > <?=$books?> livres" : {
            color: "rgba( 0, 0, 128, 0.7 )",
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
        },
        underlayCallback: function(canvas, area, g) {
          canvas.fillStyle = "rgba(192, 192, 192, 0.3)";
          var periods = [ [1789,1794], [1914,1918], [1930,1939]];
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
        { series: "♂ % né à Paris", x: "1789", shortText: "1789", width: "", height: "", cssClass: "annl", },
        { series: "♂ % né à Paris", x: "1914", shortText: "1914", width: "", height: "", cssClass: "annl", },
        { series: "♂ % né à Paris", x: "1930", shortText: "Crise", width: "", height: "", cssClass: "annl", },
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
    <div class="text">
    </div>
    <?php include ( dirname(__FILE__).'/footer.php' ) ?>
  </body>
</html>
