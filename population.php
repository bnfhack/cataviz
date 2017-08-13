<?php
$smooth = 0;
$from = 1770;
$to = 1880;
$smooth = 0;
$log = NULL;
include ( dirname(__FILE__).'/Cataviz.php' );
$db = new Cataviz( "databnf.sqlite" );

?><!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <title>Population des auteurs, Databnf.</title>
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
        <a href="" target="_new">Population des auteurs </a> 
        | <a href="?from=1760&amp;to=1860">Révolution</a>
        | <a href="?from=1860&amp;to=2017">1860–…</a>.
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
        { series: "% femmes", x: "1648", shortText: "La Fronde", width: "", height: "", cssClass: "annl", },
        { series: "% femmes", x: "1789", shortText: "1789", width: "", height: "", cssClass: "annl", },
        { series: "% femmes", x: "1815", shortText: "1815", width: "", height: "", cssClass: "annl", },
        { series: "% femmes", x: "1830", shortText: "1830", width: "", height: "", cssClass: "annl", },
        { series: "% femmes", x: "1848", shortText: "1848", width: "", height: "", cssClass: "annl", },
        { series: "% femmes", x: "1870", shortText: "1870", width: "", height: "", cssClass: "annl", },
        // { series: "% femmes", x: "1881", shortText: "Lois J. Ferry", width: "", height: "", cssClass: "ann", },
        { series: "% femmes", x: "1914", shortText: "1914", width: "", height: "", cssClass: "annl", },
        { series: "% femmes", x: "1939", shortText: "1939", width: "", height: "", cssClass: "annl", },
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
    <p>Pour chaque année, somme de tous les auteurs vivants ayant écrit un livre de plus de 50 pages. Si une personne n’a pas de date de mort, et une naissance après 1920, elle est considérée comme encore vivante actuellement. Ce calcul réagit vite à l’entrée de nouveaux auteurs dans la carrière, comme par exemple au moment d’une Révolution, par contre, il y a une grande inertie à la sortie, laissant vivre beaucoup d’auteurs d’un seul livre. Cette population ne participe peut-être moins à la vie intellectuelle, mais elles forment tout de même une masse de gens instruits qui doit peser dans le public.</p>
    <p>La part des femmes est très faible, moins de 5 % pendant des siècles. Cette proportion croît de façon continue depuis 1860, mais avant, il y a eu des points d’inflexion, notamment autour de la Révolution.</p>
    </div>
    <?php include ( dirname(__FILE__).'/footer.php' ) ?>
  </body>
</html>
