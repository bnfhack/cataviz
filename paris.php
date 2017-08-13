<?php
$from = 1860;
$to = 1960;
$smooth = 0;
include ( dirname(__FILE__).'/Cataviz.php' );
$db = new Cataviz( "databnf.sqlite" );


?><!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <title>Lieux d’édition, Paris, Databnf</title>
    <script src="lib/dygraph.min.js">//</script>
    <link rel="stylesheet" type="text/css" href="lib/dygraph.css"/>
    <link rel="stylesheet" type="text/css" href="cataviz.css"/>
    <style>
.dygraph-legend { left: 20% !important; }
.ann { transform: rotateZ(-90deg); transform-origin: 0% 100%; padding-left: 1em; border-left: none !important; border-bottom: 1px solid #000 !important; font-size: 16pt !important; font-weight: bold; color: rgba( 0, 0, 0, 0.8) !important; }
    </style>
  </head>
  <body>
    <?php include ( dirname(__FILE__).'/menu.php' ) ?>
    <header>
      <div class="links">
        <a href="?">Lieu d’édition</a> :
        <a href="?from=1760&amp;to=1860">Révolutions</a>,
        <a href="?from=1860&amp;to=1960">Guerres</a>,
        <a href="?from=1945&amp;to=2020">Présent</a>.
      </div>
      <form name="dates">
        <button onclick="window.location.href='?'; " type="button">Reset</button>
        De <input name="from" size="4" value="<?php echo $from ?>"/>
        à <input name="to" size="4" value="<?php echo  $to ?>"/>
        smooth <input name="smooth" size="2" value="<?php echo  $smooth ?>"/>
        <button type="submit">▶</button>
      </form>
    </header>

    <div id="chart" class="dygraph"></div>

    <script type="text/javascript">
    g = new Dygraph(
      document.getElementById("chart"),
      [
<?php
$qtot = $db->prepare( "SELECT count(*) AS count FROM document WHERE date = ? AND lang = 'fre'  AND type='Text' " );
$qparis = $db->prepare( "SELECT count(*) AS count FROM document WHERE date = ? AND lang = 'fre' AND type='Text' AND paris = 1 " );
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
    .", ".number_format( $paris_p, 2, '.', '')
    .", ".number_format( $notparis_p, 2, '.', '')
    .", ".number_format( (100*$nul/$tot), 2, '.', '')
  ."],\n";
}
       ?>],
      {
        labels: [ "Année", "% Paris",  "Moy. pages Paris", "Moy. pages ailleurs", "lieu ?" ],
        ylabel: "% titres en français",
        y2label: "Nombre moyen de pages",
        labelsSeparateLines: "true",
        legend: "always",
        showRoller: true,
        series: {
          "% Paris": {
            color: "rgba( 255, 0, 0, 0.5)",
            strokeWidth: 4,
          },
          "lieu ?": {
            color: "rgba( 0, 0, 0, 0.6)",
            strokeWidth: 2,
            strokePattern: [4,4],
          },
          "Moy. pages Paris": {
            axis: 'y2',
            color: "rgba( 255, 160, 160, 1)",
            strokeWidth: 2,
            fillGraph: true,
          },
          "Moy. pages ailleurs": {
            axis: 'y2',
            color: "rgba( 160, 160, 160, 1)",
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
            gridLineColor: "rgba( 128, 128, 128, 0.2)",
            gridLineWidth: 2,
            gridLinePattern: [4,6],
          },
        },
        underlayCallback: function(canvas, area, g) {
          canvas.fillStyle = "rgba(128, 128, 128, 0.3)";
          var periods = [ [1562,1598],[1648,1653], [1789,1794], [1814,1815], [1830,1831], [1848,1849], [1870,1871], [1914,1919], [1939,1945]];
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
        { series: "Moy. pages Paris", x: "1562", shortText: "Guerres de Religion", width: "", height: "", cssClass: "ann", },
        { series: "Moy. pages Paris", x: "1648", shortText: "La Fronde", width: "", height: "", cssClass: "ann", },
        { series: "Moy. pages Paris", x: "1789", shortText: "1789", width: "", height: "", cssClass: "ann", },
        { series: "Moy. pages Paris", x: "1815", shortText: "1815", width: "", height: "", cssClass: "ann", },
        { series: "Moy. pages Paris", x: "1830", shortText: "1830", width: "", height: "", cssClass: "ann", },
        { series: "Moy. pages Paris", x: "1848", shortText: "1848", width: "", height: "", cssClass: "ann", },
        { series: "Moy. pages Paris", x: "1870", shortText: "1870", width: "", height: "", cssClass: "ann", },
        { series: "Moy. pages Paris", x: "1914", shortText: "1914", width: "", height: "", cssClass: "ann", },
        { series: "Moy. pages Paris", x: "1939", shortText: "1939", width: "", height: "", cssClass: "ann", },
        { series: "Moy. pages Paris", x: "1968", shortText: "1968", width: "", height: "", cssClass: "ann", },
      ]);
    });
    </script>
    <p>Les livres ont souvent un lieu d’édition, du moins, depuis la Révolution. Durant l’Ancien-Régime, les lieux indiqués sur les pages de titres ne sont pas très fiables, aussi les statisques commencent à valoir pour le XIX<sup>e</sup> et XX<sup>e</sup> s. Les titres en français sont massivement publiés à Paris. Les villes de Province publient non seulement moins, mais des documents avec moins de pages. Les capitales francophones, Bruxelles, Genève, puis Montréal, publient certes moins, mais peuvent au moins rivaliser avec Paris par le nombre de pages moyen. Dans ce contexte, les variations de la capitale, ainsi que des lieux inconnus, sont des indicateurs historiques, sur la centralisation et l’impact des événements. Ainsi, il résulte de la <a href="?from=1760&to=1860">Révolution</a> une régularisation et une concentration de l’édition à Paris. Les <a href="?from=1860&to=1960">guerres</a> affectent le nombre de pages, mais beaucoup plus à Paris qu’ailleurs, sauf pendant la Grande-Guerre, où Lille et Bruxelles sont occupées. La <a href="?from=1945&to=2020">période actuelle</a> se caractérise par une baisse de Paris qui tient moins à la déconcentration qu’à l’apparition de nouvelles places éditoriales, comme Arles (Actes Sud, Honoré Clair, Philippe Picquier), ou l’université de Grenoble, ainsi qu’au déménagement d’éditeurs parisiens en banlieue. Le nombre de pages moyen hors Paris reste inférieur.

    </p>
    <?php include ( dirname(__FILE__).'/footer.php' ) ?>
  </body>
</html>
