<?php
// header('Content-type: text/plain; charset=utf-8');
include ( dirname(__FILE__).'/Cataviz.php' );
$db = new Cataviz( "databnf.sqlite" );
if (isset($_REQUEST['from'])) $from = $_REQUEST['from'];
else $from = 1600;
if ( $from < 1452 ) $from = 1452;
if ( $from > 2014 ) $from = 2000;
if (isset($_REQUEST['to'])) $to = $_REQUEST['to'];
else $to = 1830;
if ( $to < 1475 ) $to = 2014;
if ( $to > 2014 ) $to = 2014;

if ( isset($_REQUEST['smooth']) ) $smooth = $_REQUEST['smooth'];
else $smooth = 3;
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
      </form>
      <h1>Haine du théâtre, paysage éditorial à l’âge classique</h1>
      <a href="?">BNF, nombre titres par an, et variation du nombre moyen de pages</a>
    </header>
    <div id="chart" class="dygraph" style="width:100%; height:600px;"></div>
    <script type="text/javascript">
    g = new Dygraph(
      document.getElementById("chart"),
      [
<?php
$qtit = $db->prepare( "SELECT count(*) AS count FROM document WHERE date = ? AND type = 'Text' " );
$qpag = $db->prepare( "SELECT avg(pages) FROM document WHERE date = ?  AND type = 'Text'  " );

$data = array();
$lastpag = 0;
$lastpagfr = 0;
for ( $date=$from; $date <= $to; $date++ ) {
  $qtit->execute( array( $date ) );
  list( $tit ) = $qtit->fetch( PDO::FETCH_NUM );
  $qpag->execute( array( $date ) );
  list( $pag ) = $qpag->fetch( PDO::FETCH_NUM );
  if (!$pag) {
    $pag = $lastpag;
  }
  $lastpag = $pag;
  echo "[".$date;
  echo ", ".$tit;
    // .", ".number_format( (100*$nop/$tot), 2, '.', '')
  echo  ", ". number_format($pag, 2, '.', '') ;
  echo "],\n";
  $data[] = array( $date, $tit,  number_format($pag, 2, ',', '') );
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
            color: "rgba( 255, 0, 0, 0.5 )",
            strokeWidth: 1,
            fillGraph: true,
          },
          "Titres fr": {
            drawPoints: true,
            pointSize: 3,
            color: "rgba( 0, 0, 255, 0.5 )",
            strokeWidth: 1,
          },
          "Moy. pages": {
            axis: 'y2',
            color: "rgba( 128, 128, 128, 0.5)",
            strokeWidth: 5,
          },
          "Moy. pages fr": {
            axis: 'y2',
            color: "rgba( 128, 128, 255, 0.5)",
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
        // { series: "Moy. pages", x: "1648", shortText: "La Fronde", width: "", height: "", cssClass: "ann", },
        { series: "Moy. pages", x: "1789", shortText: "1789", width: "", height: "", cssClass: "ann", },
        { series: "Moy. pages", x: "1815", shortText: "1815", width: "", height: "", cssClass: "ann", },
        { series: "Moy. pages", x: "1830", shortText: "1830", width: "", height: "", cssClass: "ann", },
        { series: "Moy. pages", x: "1848", shortText: "1848", width: "", height: "", cssClass: "ann", },
        { series: "Moy. pages", x: "1870", shortText: "1870", width: "", height: "", cssClass: "ann", },
        { series: "Moy. pages", x: "1914", shortText: "1914", width: "", height: "", cssClass: "ann", },
        { series: "Moy. pages", x: "1939", shortText: "1939", width: "", height: "", cssClass: "ann", },
        { series: "Moy. pages", x: "1968", shortText: "1968", width: "", height: "", cssClass: "ann", },
      ]);
    });
    </script>
    <p>Données <a href="http://data.bnf.fr/semanticweb">data.bnf.fr</a> (avril 2016).</p>
    <textarea cols="50" rows="10"><?php
echo "Année\tTitres\tMoy. pages\n";
$max = count($data);
for ($i=0; $i < $max; $i++) {
  echo implode( $data[$i], "\t" )."\n";
}
    ?></textarea>
  </body>
</html>
