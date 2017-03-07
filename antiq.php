<?php
// header('Content-type: text/plain; charset=utf-8');
include ( dirname(__FILE__).'/Cataviz.php' );
$db = new Cataviz( "databnf.sqlite" );
$from = 1800;
if (isset($_REQUEST['from'])) $from = $_REQUEST['from'];
if ( $from < 1475 ) $from = 1475;
if ( $from > 2015 ) $from = 2000;
$to = 1980;
if (isset($_REQUEST['to'])) $to = $_REQUEST['to'];
if ( $to < 1475 ) $to = 2014;
if ( $to > 2015 ) $to = 2014;
if ( isset($_REQUEST['smooth']) ) $smooth = $_REQUEST['smooth'];
else $smooth = 3;
if ( $smooth < 0 ) $smooth = 0;
if ( $smooth > 50 ) $smooth = 50;
$log = 0;
if ( isset( $_REQUEST['log'] ) ) $log = 0+$_REQUEST['log'];
$max = @$_REQUEST['max'];

?><!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <script src="dygraph-combined.js">//</script>
    <link rel="stylesheet" type="text/css" href="cataviz.css"/>
    <style>
.dygraph-legend { left: 60px !important; }
    </style>
  </head>
  <body>
    <?php include ( dirname(__FILE__).'/menu.php' ) ?>
    <div>
      <form name="dates" style="float: right;">
        <button onclick="window.location.href='?'; " type="button">Reset</button>
        From <input name="from" size="4" value="<?php echo $from ?>"/>
        to <input name="to" size="4" value="<?php echo  $to ?>"/>
        <button type="submit">▶</button>
      </form>
      <a href="?">Data.bnf.fr, latin et antiquité, titres par année</a> |
      <a href="?from=1450&amp;to=1650">1450–1650</a> |
      <a href="?from=1650&amp;to=1800">1650-1800</a> |
      <a href="?from=1800&amp;to=1980">1800-1980</a>
    </div>
    <div id="chart" class="dygraph" style="width:100%; height:550px;"></div>
    <script type="text/javascript">
    g = new Dygraph(
      document.getElementById("chart"),
      [
<?php
// fre, eng, ger, ita, zxx ?, spa, lat, frm, ara, gre, chi
// part des documents avec un langue
$qgrc = $db->prepare( "SELECT count(*) AS count FROM document WHERE date = ? AND type = 'Text' AND lang = 'grc' " );
$qlatant = $db->prepare( "SELECT count(*) AS count FROM document WHERE date = ? AND type = 'Text' AND lang = 'lat' AND birthyear < 150 " );
$qlatmed = $db->prepare( "SELECT count(*) AS count FROM document WHERE date = ? AND type = 'Text' AND lang = 'lat' AND birthyear >= 150 AND birthyear < 1450  " );
$qlatmod = $db->prepare( "SELECT count(*) AS count FROM document WHERE date = ? AND type = 'Text' AND lang = 'lat' AND birthyear >= 1450  " );
$qfreant = $db->prepare( "SELECT count(*) AS count FROM document WHERE date = ? AND type = 'Text' AND (lang = 'frm' OR lang = 'fre') AND birthyear < 1400 " );

for ( $date=$from; $date <= $to; $date++ ) {


  $qlatant->execute( array( $date ) );
  $latant = current( $qlatant->fetch( PDO::FETCH_NUM ) ) ;

  $qlatmed->execute( array( $date ) );
  $latmed = current( $qlatmed->fetch( PDO::FETCH_NUM ) ) ;

  $qlatmod->execute( array( $date ) );
  $latmod = current( $qlatmod->fetch( PDO::FETCH_NUM ) ) ;

  $qgrc->execute( array( $date ) );
  $grc = current( $qgrc->fetch( PDO::FETCH_NUM ) ) ;

  $qfreant->execute( array( $date ) );
  $freant = current( $qfreant->fetch( PDO::FETCH_NUM ) ) ;

  echo "[".$date
    .", ".$freant
    .", ".$latant
    .", ".$grc
    .", ".$latmed
    .", ".$latmod
  ."],\n";
}
       ?>],
      {
        labels: [ "Année",  "Antiquité, traductions", "Latin ancien", "Grec ancien", "Latin médiéval", "Latin moderne" ],
        ylabel: "nb. de titres",
        labelsSeparateLines: "true",
        showRoller: true,
        rollPeriod: <?php echo $smooth ?>,
        legend: "always",
        strokeWidth: 5,
        logscale: <?php echo $log ?>,
        valueRange: [0, <?php echo $max ?>],
        // stackedGraph: true,
        series: {
          "Antiquité, traductions": {
            strokeWidth: 5,
            color: "rgba( 0, 0, 255, 0.3)",
          },
          "Grec ancien": {
            strokeWidth: 5,
            color: "rgba( 0, 128, 255, 0.3)",
          },
          "Latin ancien": {
            strokeWidth: 5,
            color: "rgba( 255, 0, 0, 0.3)",
          },
          "Latin médiéval": {
            strokeWidth: 5,
            color: "rgba( 128, 128, 128, 0.6)",
            strokePattern: [5, 5],
          },
          "Latin moderne": {
            strokeWidth: 5,
            color: "rgba( 0, 0, 0, 0.6)",
            strokePattern: [5, 5],
          },
        },
        axes: {
          y: {
            includeZero: true,
          },
          y2: {
            includeZero: true,
          },
        },
      }
    );
    g.ready(function() {
      g.setAnnotations([
        { series: "Antiquité, traductions", x: "1789", shortText: "1789", width: "", height: "", cssClass: "ann", },
        { series: "Antiquité, traductions", x: "1815", shortText: "1815", width: "", height: "", cssClass: "ann", },
        { series: "Antiquité, traductions", x: "1830", shortText: "1830", width: "", height: "", cssClass: "ann", },
        { series: "Antiquité, traductions", x: "1848", shortText: "1848", width: "", height: "", cssClass: "ann", },
        { series: "Antiquité, traductions", x: "1870", shortText: "1870", width: "", height: "", cssClass: "ann", },
        { series: "Antiquité, traductions", x: "1914", shortText: "1914", width: "", height: "", cssClass: "ann", },
        { series: "Antiquité, traductions", x: "1939", shortText: "1939", width: "", height: "", cssClass: "ann", },
        { series: "Antiquité, traductions", x: "1968", shortText: "1968", width: "", height: "", cssClass: "ann", },
      ]);
    });

    </script>
    <p>Données <a href="http://data.bnf.fr/semanticweb">data.bnf.fr</a> (avril 2016).</p>
    <p>Des statististiques sur la santé des classiques peuvent s’obtenir en croisant la date de naissance de l’auteur, et la langue déclarée du document. Les effectifs par an sont faibles et doivent être lissés pour ne pas brouiller les tendances. On découvrira d’abord que le latin est encore une langue très vivante. Le pape et les congrégations produisent toujours beaucoup de titres qui entrent au catalogue. Le pic de 1990 résulte du “Grand Récollement”, c’est-à-dire du déménagement de la bibliothèque nationale vers le site de Tolbiac, qui a permis de retrouver beaucoup de livres qui n’étaient pas encore catalogués. Les guerres affectent l’activité éditoriale savante, et notamment les traductions des textes antiques. L’activité de traduction commence a dépasser l’édition des originaux latins à partir de 1830.</p>
  </body>
</html>
