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
if ( $to < 1475 ) $to = 2016;
if ( $to > 2016 ) $to = 2016;

if ( isset($_REQUEST['smooth']) ) $smooth = $_REQUEST['smooth'];
else $smooth = 2;
if ( $smooth < 0 ) $smooth = 0;
if ( $smooth > 50 ) $smooth = 50;

$log = true;
if ( isset($_REQUEST['log']) ) $log = $_REQUEST['log'];

?><!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <script src="lib/dygraph.min.js">//</script>
    <link rel="stylesheet" type="text/css" href="lib/dygraph.css"/>
    <link rel="stylesheet" type="text/css" href="cataviz.css"/>
    <style>
.dygraph-legend { left: 9% !important; top: 1ex !important; }
.dygraph-ylabel { color: rgba( 0, 0, 0, 0.7 ); font-weight: normal; }
.dygraph-axis-label-y1 { color: #000; }
.dygraph-y2label { color: rgba( 128, 128, 128, 0.5); }
.dygraph-axis-label-y2 { color: rgba( 192, 192, 192, 1 ); font-weight: bold; font-size: 20px;}
.ann { transform: rotateZ(-90deg); transform-origin: 0% 100%; padding-left: 1em; border-left: none !important; border-bottom: 1px solid #000 !important; font-size: 14pt !important; font-weight: normal; color: rgba( 0, 0, 0, 0.8) !important; }
    </style>
  </head>
  <body>
    <?php include ( dirname(__FILE__).'/menu.php' ) ?>
    <header>
      <div class="links">
        <a href="" target="_new">Data.bnf.fr, titres signés par une femme</a> :
        <a href="?from=1600&amp;to=1788&amp;smooth=8">1600–1789</a>,
        <a href="?from=1760&amp;to=1960">1760–1960 guerres et révolutions</a>,
        <a href="?from=1910&amp;to=2015">XX<sup>e</sup> progression à nuancer</a>,
      </div>
      <form name="dates">
        <button onclick="window.location.href='?'; " type="button">Reset</button>
        De <input name="from" size="4" value="<?php echo $from ?>"/>
        à <input name="to" size="4" value="<?php echo  $to ?>"/>
        Échelle
        <button id="log" <?php if( $log ) echo'disabled="true"';?> type="button">log</button>
        <button id="linear" <?php if( !$log ) echo'disabled="true"';?> type="button">linéaire</button>
        <button type="submit">▶</button>
      </form>
    </header>
    <div id="chart" class="dygraph" style="width:100%; height:500px;"></div>
    <script type="text/javascript">
    g = new Dygraph(
      document.getElementById("chart"),
      [
<?php
// 844653 document 'fre' mais pas 'Text' (albums illustrés…)
$qtitf = $db->prepare( "SELECT count(*) AS count FROM document WHERE lang='fre' AND book=1 AND posthum = 0 AND gender = 2 AND document.date = ? " );
$qtith = $db->prepare( "SELECT count(*) AS count FROM document WHERE lang='fre' AND book=1 AND posthum = 0 AND gender = 1 AND document.date = ? " );
// logique un peu bizarre, mais permet de profiter de l’index birthyear au max, gens entre 20 et 70 ans (mais pas morts)
// après expérience, pas très intéressant
// $qautf = $db->prepare( "SELECT count(*) FROM person WHERE gender = 2 AND writes = 1 AND lang = 'fre' AND birthyear <= (? - 20) AND birthyear >= (?-70) AND deathyear > ? " );
// $qpagesf = $db->prepare( "SELECT avg(pages) FROM document WHERE date = ?  AND type = 'Text' AND lang='fre' AND gender=2 " );
// $qpages = $db->prepare( "SELECT avg(pages) FROM document WHERE date = ?  AND type = 'Text' AND lang='fre' " );


for ( $date=$from; $date <= $to; $date++ ) {
  $qtitf->execute( array( $date ) );
  list( $titf ) = $qtitf->fetch( PDO::FETCH_NUM );
  $qtith->execute( array( $date ) );
  list( $tith ) = $qtith->fetch( PDO::FETCH_NUM );

  echo "[".$date;
  echo ",".$tith;
  echo ",".$titf;
  echo ",".number_format( 100.0*($titf/( $tith+$titf )), 2, '.', '');
  // echo ",".$titf;
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
        labels: [ "Année", "♂ titres", "♀ titres", "% des femmes" ],
        legend: "always",
        labelsSeparateLines: "true",
        ylabel: "Titres",
        y2label: "Part des titres %",
        showRoller: true,
        rollPeriod: <?php echo $smooth ?>,
        <?php if ($log) echo "logscale: true,";  ?>
        series: {
          "♂ titres": {
            // drawPoints: true,
            // pointSize: 3,
            color: "rgba( 0, 0, 192, 1 )",
            strokeWidth: 4,
          },
          "♀ titres": {
            color: "rgba( 255, 128, 128, 1 )",
            strokeWidth: 4,
          },
          "% des femmes": {
            axis: 'y2',
            color: "rgba( 128, 128, 128, 1 )",
            strokeWidth: 1,
            fillGraph: true,
          },
        },
        axes: {
          x: {
            gridLineWidth: 2,
            drawGrid: false,
            independentTicks: true,
          },
          y: {
            independentTicks: true,
            drawGrid: true,
            gridLineColor: "rgba( 192, 192, 192, 0.5 )",
            gridLineWidth: 1,
          },
          y2: {
            independentTicks: true,
            drawGrid: true,
            gridLineColor: "rgba( 192, 192, 192, 0.4)",
            gridLineWidth: 6,
            gridLinePattern: [6,6],
          },
        },
        underlayCallback: function(canvas, area, g) {
          canvas.fillStyle = "rgba(255, 128, 0, 0.2)";
          var periods = [ [1648,1653],[1789,1795], [1814,1815], [1830,1831], [1848,1849], [1870,1871], [1914,1918], [1939,1945]];
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
        { series: "% des femmes", x: "1648", shortText: "La Fronde", width: "", height: "", cssClass: "ann", },
        { series: "% des femmes", x: "1789", shortText: "1789", width: "", height: "", cssClass: "ann", },
        { series: "% des femmes", x: "1815", shortText: "1815", width: "", height: "", cssClass: "ann", },
        { series: "% des femmes", x: "1830", shortText: "1830", width: "", height: "", cssClass: "ann", },
        { series: "% des femmes", x: "1848", shortText: "1848", width: "", height: "", cssClass: "ann", },
        { series: "% des femmes", x: "1870", shortText: "1870", width: "", height: "", cssClass: "ann", },
        { series: "% des femmes", x: "1914", shortText: "1914", width: "", height: "", cssClass: "ann", },
        { series: "% des femmes", x: "1939", shortText: "1939", width: "", height: "", cssClass: "ann", },
        { series: "% des femmes", x: "1968", shortText: "1968", width: "", height: "", cssClass: "ann", },
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
    <p>Par an, la part des titres en français signés par une femme est très basse jusqu’au XX<sup>e</sup> s., &lt; 5%. On observe une progression sur le temps long, pour atteindre 1/3  de nos jours. La proportion de titres féminins baisse pendant les guerres et les révolutions, montrant bien qu’en période de restriction de papier, les hommes passent avant.</p>
    <?php include ( dirname(__FILE__).'/footer.php' ) ?>
  </body>
</html>
