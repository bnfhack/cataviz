<?php
// header('Content-type: text/plain; charset=utf-8');
include ( dirname(__FILE__).'/Cataviz.php' );
$db = new Cataviz( "databnf.db" );
$from = 1910;
if (isset($_REQUEST['from'])) $from = $_REQUEST['from'];
if ( $from < 1475 ) $from = 1475;
if ( $from > 2014 ) $from = 2000;

$to = 1985;
if (isset($_REQUEST['to'])) $to = $_REQUEST['to'];
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
    </style>
  </head>
  <body>
    <?php include ( dirname(__FILE__).'/menu.php' ) ?>
    <form name="dates" style="float: right;">
      <button onclick="window.location.href='?'; " type="button">Reset</button>
      From <input name="from" size="4" value="<?php echo $from ?>"/>
      to <input name="to" size="4" value="<?php echo  $to ?>"/>
      <button type="submit">GO</button>
    </form>
    <div class="titre">
      <a href="" target="_new">Data.bnf.fr, documents d‘un auteur mort (toutes langues confondues)</a>
      <a href="?from=1475&amp;to=1780&amp;smooth=10">1475–1780</a>,
      <a href="?from=1780&amp;to=1860">1780–1860</a>,
      <a href="?from=1860&amp;to=1960">1860–1960</a>,
      <a href="?from=1960&amp;to=2015">1960–…</a>
    </div>
<?php
?>
    <div id="chart" class="dygraph" style="width:100%; height:600px;"></div>
    <script type="text/javascript">
    g = new Dygraph(
      document.getElementById("chart"),
      [
<?php


$qdead = $db->prepare( "SELECT count(*) AS count FROM document WHERE date = ? AND type = 'Text' AND posthum=1 " );
$qdeadfr = $db->prepare( "SELECT count(*) AS count FROM document WHERE date = ? AND type = 'Text' AND posthum=1 AND lang='fre' " );
$qtot = $db->prepare( "SELECT count(*) AS count FROM document WHERE date = ? AND type = 'Text'   " );
$qtot_p = $db->prepare( "SELECT avg(pages) FROM document WHERE date = ?  AND type = 'Text'" );
$qdead_p = $db->prepare( "SELECT avg(pages) FROM document WHERE date = ?  AND type = 'Text' AND posthum=1 " );


$adate = array();
$atot = array();
$adead = array();
$adeadfr = array();
$arate = array();

$atot_p = array();
$adead_p = array();


for ( $date=$from; $date <= $to; $date++ ) {
  $adate[] = $date;

  $qtot->execute( array( $date ) );
  $tot = current( $qtot->fetch( PDO::FETCH_NUM ) );
  $atot[] = $tot;
  $qdead->execute( array( $date ) );
  $dead = current( $qdead->fetch( PDO::FETCH_NUM ) );
  $adead[] = $dead;
  $arate[] = $dead / $tot;
  // $qdeadfr->execute( array( $date ) );
  // $adeadfr[] = current( $qdeadfr->fetch( PDO::FETCH_NUM ) );

  /*
  $qtot_p->execute( array( $date ) );
  $atot_p[] = current( $qtot_p->fetch( PDO::FETCH_NUM ) );
  $qdead_p->execute( array( $date ) );
  $adead_p[] = current( $qdead_p->fetch( PDO::FETCH_NUM ) );
  */
}
$size = count( $adate );
$rate = array_sum( $arate ) / $size ;
for ( $i=0; $i < $size; $i++ ) {
  /**
  $ifrom = $i - $smooth;
  if ( $ifrom < 0 ) $ifrom = 0;
  $ito = $i + $smooth;
  if ( $ito > $size ) $ito = $size;
  $iwidth = 1+$ito-$ifrom;

  $tot = array_sum( array_slice( $atot, $ifrom, $iwidth ) ) / $iwidth;
  $dead = array_sum( array_slice( $adead, $ifrom, $iwidth ) ) / $iwidth;
  $deadfr = array_sum( array_slice( $adeadfr, $ifrom, $iwidth ) ) / $iwidth;
  // $tot_p = array_sum( array_slice( $atot_p, $ifrom, $iwidth ) ) / $iwidth;
  // $dead_p = array_sum( array_slice( $adead_p, $ifrom, $iwidth ) ) / $iwidth;
  */

  echo "[".$adate[$i]
    .", ".$adead[$i]
     .", ".( $atot[$i] * $rate )
    // .", ".$deadfr
    // .", ".$tot_p
     .", ".number_format( ( 100*$adead[$i] / $atot[$i] ), 2, '.', '')
  ."],\n";
}

       ?>],
      {
        labels: [ "Année", "Posthumes", "Variation du total", "% de posthumes" ],
        legend: "always",
        labelsSeparateLines: "true",
        ylabel: "Posthumes",
        y2label: "% de posthumes",
        showRoller: true,
        rollPeriod: <?php echo $smooth ?>,
        series: {
          "Variation du total": {
            axis: 'y',
            color: "rgba( 0, 0, 0, 0.8)",
            strokeWidth: 1,
            strokePattern: [4, 4],
          },
          "Posthumes": {
            axis: 'y',
            strokeWidth: 2,
            color: "rgba( 255, 0, 0, 0.8)",
          },
          "Posthumes fr.": {
            axis: 'y',
            strokeWidth: 1,
            color: "rgba( 0, 0, 128, 0.5)",
          },
          "% de posthumes": {
            axis: 'y2',
            color: "rgba( 255, 0, 0, 0.1)",
            strokeWidth: 5,
          },
        },
        axes: {
          y: {
            includeZero: true,
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
        { series: "% de posthumes", x: "1789", shortText: "1789", width: "", height: "", cssClass: "ann", },
        { series: "% de posthumes", x: "1815", shortText: "1815", width: "", height: "", cssClass: "ann", },
        { series: "% de posthumes", x: "1830", shortText: "1830", width: "", height: "", cssClass: "ann", },
        { series: "% de posthumes", x: "1848", shortText: "1848", width: "", height: "", cssClass: "ann", },
        { series: "% de posthumes", x: "1870", shortText: "1870", width: "", height: "", cssClass: "ann", },
        { series: "% de posthumes", x: "1914", shortText: "1914", width: "", height: "", cssClass: "ann", },
        { series: "% de posthumes", x: "1939", shortText: "1939", width: "", height: "", cssClass: "ann", },
        { series: "% de posthumes", x: "1968", shortText: "1968", width: "", height: "", cssClass: "ann", },
      ]);
    });
    </script>
    <p>Données <a href="http://data.bnf.fr/semanticweb">data.bnf.fr</a> (avril 2016).</p>
  </body>
</html>
