<?php
// header('Content-type: text/plain; charset=utf-8');
include ( dirname(__FILE__).'/Cataviz.php' );
$db = new Cataviz( "databnf.sqlite" );
if (isset($_REQUEST['from'])) $from = $_REQUEST['from'];
else $from = 1800;
if ( $from < 1475 ) $from = 1475;
if ( $from > 2015 ) $from = 2000;
if (isset($_REQUEST['to'])) $to = $_REQUEST['to'];
else $to = 2015;
if ( $to < 1475 ) $to = 2015;
if ( $to > 2015 ) $to = 2015;
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
    <form name="dates" style="float: right;">
      <button onclick="window.location.href='?'; " type="button">Reset</button>
      From <input name="from" size="4" value="<?php echo $from ?>"/>
      to <input name="to" size="4" value="<?php echo  $to ?>"/>
      smooth <input name="smooth" size="2" value="<?php echo  $smooth ?>"/>
      <button type="submit">GO</button>
    </form>
    <a href="" target="_new">Data.bnf.fr : Paris, place principale de publication des documents en français</a>
    <a href="?from=1475&amp;to=1780&amp;smooth=2">1475–1780</a>,
    <a href="?from=1780&amp;to=1890">1780–1890</a>,
    <a href="?from=1860&amp;to=1960">1860–1960</a>,
    <a href="?from=1960&amp;to=2015">1960–…</a>
    <div id="chart" class="dygraph" style="width:100%; height:550px;"></div>
    <script type="text/javascript">
    g = new Dygraph(
      document.getElementById("chart"),
      [
<?php
$qtot = $db->prepare( "SELECT count(*) AS count FROM document WHERE date = ? AND lang = 'fre'  AND type='Text' " );
$qparis = $db->prepare( "SELECT count(*) AS count FROM document WHERE date = ? AND lang = 'fre' AND type='Text' AND paris = 1; " );
$qnul = $db->prepare( "SELECT count(*) AS count FROM document WHERE date = ? AND lang = 'fre' AND type='Text' AND place IS NULL; " );
$qno_p = $db->prepare( "SELECT count(*) AS count FROM document WHERE date = ? AND lang = 'fre' AND type='Text' AND pages IS NULL; " );
$qparis_p = $db->prepare( "SELECT avg(pages) FROM document WHERE date = ?  AND type = 'Text' AND lang = 'fre' AND paris = 1;" );
$qnotparis_p = $db->prepare( "SELECT avg(pages) FROM document WHERE date = ?  AND type = 'Text' AND lang = 'fre' AND paris IS NULL;" );
$adate = array();
$atot = array();
$aparis = array();
$anul = array();
$ano_p = array();
$aparis_p = array();
$anotparis_p = array();
for ( $date=$from; $date <= $to; $date++ ) {
  $adate[] = $date;
  $qtot->execute( array( $date ) );
  $atot[] = current( $qtot->fetch( PDO::FETCH_NUM ) ) ;
  $qparis->execute( array( $date ) );
  $aparis[] = current( $qparis->fetch( PDO::FETCH_NUM ) );
  $qnul->execute( array( $date ) );
  $anul[] = current( $qnul->fetch( PDO::FETCH_NUM ) );
  $qno_p->execute( array( $date ) );
  $ano_p[] = current( $qno_p->fetch( PDO::FETCH_NUM ) );
  $qparis_p->execute( array( $date ) );
  $aparis_p[] = current( $qparis_p->fetch( PDO::FETCH_NUM ) );
  $qnotparis_p->execute( array( $date ) );
  $anotparis_p[] = current( $qnotparis_p->fetch( PDO::FETCH_NUM ) );
}
$size = count( $adate );
for ( $i=0; $i < $size; $i++ ) {
  $ifrom = $i - $smooth;
  if ( $ifrom < 0 ) $ifrom = 0;
  $ito = $i + $smooth;
  if ( $ito > $size ) $ito = $size;
  $iwidth = 1+$ito-$ifrom;

  $tot = array_sum( array_slice( $atot, $ifrom, $iwidth ) ) / $iwidth;
  $paris = array_sum( array_slice( $aparis, $ifrom, $iwidth ) ) / $iwidth;
  $nul = array_sum( array_slice( $anul, $ifrom, $iwidth ) ) / $iwidth;
  // $no_p = array_sum( array_slice( $ano_p, $ifrom, $iwidth ) ) / $iwidth;
  $paris_p = array_sum( array_slice( $aparis_p, $ifrom, $iwidth ) ) / $iwidth;
  $notparis_p = array_sum( array_slice( $anotparis_p, $ifrom, $iwidth ) ) / $iwidth;
  echo "[".$adate[$i]
    .", ".number_format( (100*$paris/$tot), 2, '.', '')
    .", ".number_format( (100*$nul/$tot), 2, '.', '')
    .", ".number_format( $paris_p, 2, '.', '')
    .", ".number_format( $notparis_p, 2, '.', '')
  ."],\n";
}
       ?>],
      {
        labels: [ "Année", "Paris", "lieu ?",  "Moy. pages Paris", "Moy. pages ailleurs" ],
        ylabel: "% documents en français",
        y2label: "Nombre moyen de pages",
        labelsSeparateLines: "true",
        legend: "always",
        series: {
          "Paris": {
            color: "rgba( 255, 0, 0, 0.8)",
            strokeWidth: 3,
          },
          "lieu ?": {
            color: "rgba( 0, 0, 0, 0.6)",
            strokeWidth: 2,
            strokePattern: [4,4],
          },
          "Moy. pages Paris": {
            axis: 'y2',
            color: "rgba( 255, 0, 0, 0.2)",
            strokeWidth: 5,
          },
          "Moy. pages ailleurs": {
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
        },
      }
    );
    g.ready(function() {
      g.setAnnotations([
        { series: "Moy. pages Paris", x: "1591", shortText: "Guerres de religion", width: "", height: "", cssClass: "ann", },
        { series: "Moy. pages Paris", x: "1789", shortText: "1789", width: "", height: "", cssClass: "ann", },
        { series: "Moy. pages Paris", x: "1830", shortText: "1830", width: "", height: "", cssClass: "ann", },
        { series: "Moy. pages Paris", x: "1848", shortText: "1848", width: "", height: "", cssClass: "ann", },
        { series: "Moy. pages Paris", x: "1870", shortText: "1870", width: "", height: "", cssClass: "ann", },
        { series: "Moy. pages Paris", x: "1914", shortText: "1914", width: "", height: "", cssClass: "ann", },
        { series: "Moy. pages Paris", x: "1939", shortText: "1939", width: "", height: "", cssClass: "ann", },
        { series: "Moy. pages Paris", x: "1968", shortText: "1968", width: "", height: "", cssClass: "ann", },
      ]);
    });
    </script>
    <p>Données <a href="http://data.bnf.fr/semanticweb">data.bnf.fr</a> (avril 2016).</p>
    <p>Entre 60% et 80% des titres sont publiés à Paris, avec généralement un nombre de pages moyen plus élevé que pour les autres lieux d’édition.</p>
  </body>
</html>
