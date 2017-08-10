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
else $smooth = 5;
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
.dygraph-legend { left: 8% !important; }
.dygraph-ylabel { color: rgba( 0, 0, 0, 0.7 ); font-weight: normal; }
.dygraph-axis-label-y1 { color: #000; }
.dygraph-y2label { color: rgba( 128, 128, 128, 0.5); }
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
        <button id="log" type="button">log</button>
        <button id="linear" disabled="true" type="button">linéaire</button>
        <button type="submit">▶</button>
      </form>
    </header>
    <div id="chart" class="dygraph" style="width:100%; height:400px;"></div>
    <script type="text/javascript">
    g = new Dygraph(
      document.getElementById("chart"),
      [
<?php
// 844653 document 'fre' mais pas 'Text' (albums illustrés…)
$qtitf = $db->prepare( "SELECT count(*) AS count FROM document WHERE document.date = ? AND type='Text' AND lang='fre' AND gender = 2 " );
$qtith = $db->prepare( "SELECT count(*) AS count FROM document WHERE document.date = ? AND type='Text' AND lang='fre' AND gender = 1 " );
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
            strokeWidth: 2,
          },
          "♀ titres": {
            color: "rgba( 255, 128, 128, 1 )",
            strokeWidth: 2,
          },
          "% des femmes": {
            axis: 'y2',
            color: "rgba( 128, 128, 128, 0.5)",
            strokeWidth: 4,
            // strokePattern: [4,4],
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
            gridLineColor: "rgba( 128, 128, 128, 0.5 )",
            gridLineWidth: 1,
          },
          y2: {
            independentTicks: true,
            drawGrid: true,
            gridLineColor: "rgba( 128, 128, 128, 0.3)",
            gridLineWidth: 2,
            gridLinePattern: [4,4],
          },
        }
      }
    );
    g.ready(function() {
      g.setAnnotations([
        { series: "♂ titres", x: "1648", shortText: "La Fronde", width: "", height: "", cssClass: "ann", },
        { series: "♂ titres", x: "1788", shortText: "1788", width: "", height: "", cssClass: "ann", },
        { series: "♂ titres", x: "1793", shortText: "1793", width: "", height: "", cssClass: "ann", },
        { series: "♂ titres", x: "1815", shortText: "1815", width: "", height: "", cssClass: "ann", },
        { series: "♂ titres", x: "1830", shortText: "1830", width: "", height: "", cssClass: "ann", },
        { series: "♂ titres", x: "1848", shortText: "1848", width: "", height: "", cssClass: "ann", },
        { series: "♂ titres", x: "1869", shortText: "1869", width: "", height: "", cssClass: "ann", },
        { series: "♂ titres", x: "1913", shortText: "1913", width: "", height: "", cssClass: "ann", },
        { series: "♂ titres", x: "1939", shortText: "1939", width: "", height: "", cssClass: "ann", },
        { series: "♂ titres", x: "1968", shortText: "1968", width: "", height: "", cssClass: "ann", },
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
