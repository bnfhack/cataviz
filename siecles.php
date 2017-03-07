<?php
// header('Content-type: text/plain; charset=utf-8');
include ( dirname(__FILE__).'/Cataviz.php' );
$db = new Cataviz( "databnf.sqlite" );
$from = 1865;
if (isset($_REQUEST['from'])) $from = $_REQUEST['from'];
if ( $from < 1475 ) $from = 1475;
if ( $from > 2015 ) $from = 2000;
$to = 1960;
if (isset($_REQUEST['to'])) $to = $_REQUEST['to'];
if ( $to < 1475 ) $to = 2014;
if ( $to > 2014 ) $to = 2014;
if ( isset($_REQUEST['smooth']) ) $smooth = $_REQUEST['smooth'];
else $smooth = 0;
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
#header { min-height: 2.7em;  }
    </style>
  </head>
  <body>
    <?php include ( dirname(__FILE__).'/menu.php' ) ?>
    <header id="header">
      <form name="dates" style="float: right; text-align: right;">
        <button onclick="window.location.href='?'; " type="button">Reset</button>
        From <input name="from" size="4" value="<?php echo $from ?>"/>
        to <input name="to" size="4" value="<?php echo  $to ?>"/>
        <button type="submit">▶</button>
        <br/>
        Zoom
        <button type="button" onclick="var options = {}; var max = g.yAxisRange()[1] /1.5; options.valueRange = [ 0, max]; g.updateOptions(options); ">▼</button>
        <button type="button" onclick="var options = {}; var max = g.yAxisRange()[1] *1.5; options.valueRange = [ 0, max]; g.updateOptions(options); ">▲</button>
      </form>
      <div>
        <a href="?">Titres d’auteurs morts à la date de publicationn, colorés par siècles</a> |
        <a href="?from=1450&amp;to=1640&amp;smooth=4">1450–1640</a> |
        <a href="?from=1640&amp;to=1780&amp;smooth=4">1640-1780</a> |
        <a href="?from=1780&amp;to=1865">1780-1865</a> |
        <a href="?from=1865&amp;to=1962">1865-1962</a>
      </div>
    </header>
    <div id="chart" class="dygraph" style="width:100%; height:550px;"></div>
    <script type="text/javascript">
    g = new Dygraph(
      document.getElementById("chart"),
      [
<?php
// fre, eng, ger, ita, zxx ?, spa, lat, frm, ara, gre, chi
// part des documents avec un langue
$qlive = $db->prepare( "SELECT count(*) AS count FROM document WHERE date = ? AND type = 'Text' AND posthum IS NULL; " );
$qant = $db->prepare( "SELECT count(*) AS count FROM document WHERE date = ? AND type = 'Text' AND posthum=1 AND birthyear < 150; " );
$q500 = $db->prepare( "SELECT count(*) AS count FROM document WHERE date = ? AND type = 'Text' AND posthum=1 AND birthyear >= 150 AND birthyear < 1450; " );
$q1450 = $db->prepare( "SELECT count(*) AS count FROM document WHERE date = ? AND type = 'Text' AND posthum=1 AND birthyear >= 1450 AND birthyear < 1600; " );
$q1600 = $db->prepare( "SELECT count(*) AS count FROM document WHERE date = ? AND type = 'Text' AND posthum=1 AND birthyear >= 1600 AND birthyear < 1680; " );
$q1690 = $db->prepare( "SELECT count(*) AS count FROM document WHERE date = ? AND type = 'Text' AND posthum=1 AND birthyear >= 1680 AND birthyear < 1780; " );
$q1780 = $db->prepare( "SELECT count(*) AS count FROM document WHERE date = ? AND type = 'Text' AND posthum=1 AND birthyear >= 1780 AND birthyear < 1880; " );
$q1880 = $db->prepare( "SELECT count(*) AS count FROM document WHERE date = ? AND type = 'Text' AND posthum=1 AND birthyear >= 1880 ; " );

for ( $date=$from; $date <= $to; $date++ ) {

  // $qtot->execute( array( $date ) );
  // $atot[] = current( $qtot->fetch( PDO::FETCH_NUM ) ) ;
  $qlive->execute( array( $date ) );
  $live = current( $qlive->fetch( PDO::FETCH_NUM ) ) ;

  $qant->execute( array( $date ) );
  $ant = current( $qant->fetch( PDO::FETCH_NUM ) );

  $q500->execute( array( $date ) );
  $f500 = current( $q500->fetch( PDO::FETCH_NUM ) ) ;

  $q1450->execute( array( $date ) );
  $f1450 = current( $q1450->fetch( PDO::FETCH_NUM ) ) ;

  $q1600->execute( array( $date ) );
  $f1600 = current( $q1600->fetch( PDO::FETCH_NUM ) ) ;

  $q1690->execute( array( $date ) );
  $f1690 = current( $q1690->fetch( PDO::FETCH_NUM ) ) ;

  $q1780->execute( array( $date ) );
  $f1780 = current( $q1780->fetch( PDO::FETCH_NUM ) ) ;

  $q1880->execute( array( $date ) );
  $f1880 = current( $q1880->fetch( PDO::FETCH_NUM ) ) ;


  echo "[".$date
    .", ".$live
    .", ".$f1880
    .", ".$f1780
    .", ".$f1690
    .", ".$f1600
    .", ".$f1450
    .", ".$f500
    .", ".$ant
  ."],\n";
}
       ?>],
      {
        labels: [ "Année", "Vivants", "XXe","XIXe", "XVIIIe", "XVIIe", "Renaissance", "Moyen-Âge", "Antiquité" ],
        ylabel: "Titres des morts",
        y2label: "Titres des vivants",
        showRoller: true,
        rollPeriod: <?php echo $smooth ?>,
        legend: "always",
        strokeWidth: 1,
        logscale: <?php echo $log ?>,
        valueRange: [0, <?php echo $max ?>],
        stackedGraph: true,
        series: {
          "Antiquité": {
            color: "#00F",
          },
          "Moyen-Âge": {
            color: "#80F",
          },
          "Renaissance": {
            color: "#F08",
          },
          "XVIIe": {
            color: "#F00",
          },
          "XVIIIe": {
            color: "#F80",
          },
          "XIXe": {
            color: "#080",
          },
          "XXe": {
            color: "#008",
          },
          "Vivants": {
            color: "#666",
            strokeWidth: 4,
            axis: 'y2',
            strokePattern: [4, 1],
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
            includeZero: true,
          },
        },
      }
    );
    g.ready(function() {
      g.setAnnotations([
        { series: "Vivants", x: "1648", shortText: "La Fronde", width: "", height: "", cssClass: "ann", },
        { series: "Vivants", x: "1789", shortText: "1789", width: "", height: "", cssClass: "ann", },
        { series: "Vivants", x: "1815", shortText: "1815", width: "", height: "", cssClass: "ann", },
        { series: "Vivants", x: "1830", shortText: "1830", width: "", height: "", cssClass: "ann", },
        { series: "Vivants", x: "1848", shortText: "1848", width: "", height: "", cssClass: "ann", },
        { series: "Vivants", x: "1870", shortText: "1870", width: "", height: "", cssClass: "ann", },
        { series: "Vivants", x: "1914", shortText: "1914", width: "", height: "", cssClass: "ann", },
        { series: "Vivants", x: "1939", shortText: "1939", width: "", height: "", cssClass: "ann", },
      ]);
    });
    </script>
    <p>Données <a href="http://data.bnf.fr/semanticweb">data.bnf.fr</a> (avril 2016).</p>
    <p>Une fois stabilisé, le nombre de titres d’un siècle varie assez peu, c’est-à-dire que le nombre de documents attribués à un auteur du XVII<sup>e</sup> s. est relativement stable au XIX<sup>e</sup> et au XX<sup>e</sup> s, même si le nombre de titres publiés est 10 fois plus important. L‘espace supplémentaire est occupé par les nouveautés. La réédition des titres anciens est affectée par les guerres, comme les nouveautés, on observera le profil très particulier après 1945, où la réédition reprend beaucoup plus fort qu’après 1918, politique volontariste du Conceil National de la Résistance. Le pic de 1990 résulte du “Grand Récollement”, c’est-à-dire du déménagement de la bibliothèque nationale vers le site de Tolbiac, qui a permis de retrouver beaucoup de livres qui n’étaient pas encore ou mal catalogués.</p>
  </body>
</html>
