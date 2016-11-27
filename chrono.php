<?php
// header('Content-type: text/plain; charset=utf-8');
include ( dirname(__FILE__).'/Cataviz.php' );
$db = new Cataviz( "databnf.db" );
if (isset($_REQUEST['from'])) $from = $_REQUEST['from'];
else $from = 1750;
if ( $from < 1452 ) $from = 1452;
if ( $from > 2014 ) $from = 2000;
if (isset($_REQUEST['to'])) $to = $_REQUEST['to'];
else $to = 1960;
if ( $to < 1475 ) $to = 2014;
if ( $to > 2014 ) $to = 2014;

if ( isset($_REQUEST['smooth']) ) $smooth = $_REQUEST['smooth'];
else $smooth = 0;
if ( $smooth < 0 ) $smooth = 0;
if ( $smooth > 50 ) $smooth = 50;


?><!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <script src="dygraph-combined.js">//</script>
    <link rel="stylesheet" type="text/css" href="cataviz.css"/>
    <style>
.dygraph-legend { left: 40% !important; }
    </style>
  </head>
  <body>
    <?php include ( dirname(__FILE__).'/menu.php' ) ?>
    <header style="min-height: 2.7em; ">
      <form name="dates" style="float: right; width: 30ex; ">
        <button onclick="window.location.href='?'; " type="button">Reset</button>
        De <input name="from" size="4" value="<?php echo $from ?>"/>
        à <input name="to" size="4" value="<?php echo  $to ?>"/>
        <button type="submit">▶</button>
        <br/>Échelle
        <button id="log" type="button">log</button>
        <button id="linear" disabled="true" type="button">linéaire</button>
      </form>
      <a href="?">BNF, titres en français par an, moyenne du nombre de pages</a> :
      <a href="?from=1600&amp;to=1788&amp;smooth=2">1600–1789</a>,
      <a href="?from=1780&amp;to=1860">1789–1870</a>,
      <a href="?from=1860&amp;to=1966">1870–1962</a>,
      <a href="?from=1950&amp;to=2015">1950–…</a>,
    </header>
    <div id="chart" class="dygraph" style="width:100%; height:600px;"></div>
    <script type="text/javascript">
    g = new Dygraph(
      document.getElementById("chart"),
      [
<?php
if ( $from < 1610 )
  $qtot = $db->prepare( "SELECT count(*) AS count FROM document WHERE date = ? AND type = 'Text' AND (lang='fre' OR lang='frm') " );
else
  $qtot = $db->prepare( "SELECT count(*) AS count FROM document WHERE date = ? AND type = 'Text' AND lang='fre' " );
if ( $from < 1610 )
  $qpages = $db->prepare( "SELECT avg(pages) FROM document WHERE date = ?  AND type = 'Text' AND (lang='fre'  OR lang='frm')" );
else
  $qpages = $db->prepare( "SELECT avg(pages) FROM document WHERE date = ?  AND type = 'Text' AND lang='fre'" );

$lastpages = 0;
for ( $date=$from; $date <= $to; $date++ ) {
  $qtot->execute( array( $date ) );
  list( $tot ) = $qtot->fetch( PDO::FETCH_NUM );
  $qpages->execute( array( $date ) );
  list( $pages ) = $qpages->fetch( PDO::FETCH_NUM );
  if (!$pages) {
    $pages = $lastpages;
  }
  else {
    $lastpages = $pages;
  }
  echo "[".$date.", ".$tot;
    // .", ".number_format( (100*$nop/$tot), 2, '.', '')
  echo  ", ".$pages;
  echo "],\n";
}

       ?>],
      {
        labels: [ "Année", "Titres", "Moy. pages" ],
        legend: "always",
        labelsSeparateLines: "true",
        ylabel: "Titres",
        y2label: "Pages",
        showRoller: true,
        rollPeriod: <?php echo $smooth ?>,
        series: {
          "Titres": {
            drawPoints: true,
            pointSize: 3,
            color: "rgba( 255, 0, 0, 0.6 )",
            strokeWidth: 1,
          },
          "Moy. pages": {
            axis: 'y2',
            color: "rgba( 128, 128, 128, 0.5)",
            strokeWidth: 5,
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
            gridLineColor: "rgba( 0, 0, 0, 0.5)",
            gridLineWidth: 1,
          },
          y2: {
            independentTicks: true,
            drawGrid: true,
            gridLineColor: "rgba( 128, 128, 128, 0.1)",
            gridLineWidth: 5,
          },
        }
      }
    );
    g.ready(function() {
      g.setAnnotations([
        { series: "Moy. pages", x: "1648", shortText: "La Fronde", width: "", height: "", cssClass: "ann", },
        { series: "Moy. pages", x: "1789", shortText: "1789", width: "", height: "", cssClass: "ann", },
        { series: "Moy. pages", x: "1793", shortText: "1793", width: "", height: "", cssClass: "ann", },
        { series: "Moy. pages", x: "1815", shortText: "1815", width: "", height: "", cssClass: "ann", },
        { series: "Moy. pages", x: "1830", shortText: "1830", width: "", height: "", cssClass: "ann", },
        { series: "Moy. pages", x: "1848", shortText: "1848", width: "", height: "", cssClass: "ann", },
        { series: "Moy. pages", x: "1870", shortText: "1870", width: "", height: "", cssClass: "ann", },
        { series: "Moy. pages", x: "1914", shortText: "1914", width: "", height: "", cssClass: "ann", },
        { series: "Moy. pages", x: "1939", shortText: "1939", width: "", height: "", cssClass: "ann", },
        { series: "Moy. pages", x: "1968", shortText: "1968", width: "", height: "", cssClass: "ann", },
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
    <p>Données <a href="http://data.bnf.fr/semanticweb">data.bnf.fr</a> (avril 2016).</p>
    <p>Pour l’Ancien-Régime, l’agitation de la Fronde et de la Révolution produit plus de titres mais avec moins de pages (moins de périodiques qu’au XIX<sup>e</sup>). Ensuite, les guerres et le révolutions affectent généralement beaucoup le nombre de titres et de pages (restrictions de papier, blocage de Paris).</p>
  </body>
</html>
