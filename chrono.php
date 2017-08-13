<?php
$from = 1780;
$to = 1958;
include ( dirname(__FILE__).'/Cataviz.php' );
$db = new Cataviz( "databnf.sqlite" );

$pagefloor = 50;
if ( isset($_REQUEST['pagefloor']) ) $pagefloor = $_REQUEST['pagefloor'];

?><!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <title>Chronologie, Databnf</title>
    <script src="lib/dygraph.min.js">//</script>
    <link rel="stylesheet" type="text/css" href="lib/dygraph.css"/>
    <link rel="stylesheet" type="text/css" href="cataviz.css"/>
    <style>
.dygraph-legend { left: 20% !important; }
.ann { transform: rotateZ(-90deg); transform-origin: 0% 100%; padding-left: 1em; border-left: none !important; border-bottom: 1px solid #000 !important; font-size: 14pt !important; font-weight: normal; color: rgba( 0, 0, 0, 0.8) !important; }
    </style>
  </head>
  <body>
    <?php include ( dirname(__FILE__).'/menu.php' ) ?>
    <header style="min-height: 2.7em; ">
      <div class="links">
        <a href="?">Data.bnf.fr, titres en français par an, moyenne du nombre de pages</a> :
        <a href="?from=1600&amp;to=1788&amp;smooth=2">1600–1789</a>,
        <a href="?from=1780&amp;to=1860&amp;smooth=1">1789–1870</a>,
        <a href="?from=1860&amp;to=1958&amp;smooth=1">1870–1960</a>,
        <a href="?from=1950&amp;to=2015&amp;smooth=1">1950–…</a>
      </div>
      <form name="dates">
        <button onclick="window.location.href='?'; " type="button">Reset</button>
        De <input name="from" size="4" value="<?php echo $from ?>"/>
        à <input name="to" size="4" value="<?php echo  $to ?>"/>
        <label>Seuil pages <input name="pagefloor" size="4" value="<?php echo  $pagefloor ?>"/></label>
        <label>Lissage <input name="smooth" size="1" value="<?php echo  $smooth ?>"/></label>
        Échelle
        <button id="log" <?php if( $log ) echo'disabled="true"';?> type="button">log</button>
        <button id="linear" <?php if( !$log ) echo'disabled="true"';?> type="button">linéaire</button>
        <button type="submit">▶</button>
      </form>
    </header>
    <div id="chart" class="dygraph"></div>
    <script type="text/javascript">
    g = new Dygraph(
      document.getElementById("chart"),
      [
<?php

if ( $from < 1610 )
  $qbook = $db->prepare( "SELECT count(*) AS count FROM document WHERE date = ? AND type = 'Text' AND pages > ? AND (lang='fre' OR lang='frm') " );
else
  $qbook = $db->prepare( "SELECT count(*) AS count FROM document WHERE date = ? AND type = 'Text' AND pages > ? AND lang='fre' " );

if ( $from < 1610 )
  $qbroch = $db->prepare( "SELECT count(*) AS count FROM document WHERE date = ? AND type = 'Text' AND pages <= ? AND (lang='fre' OR lang='frm') " );
else
  $qbroch = $db->prepare( "SELECT count(*) AS count FROM document WHERE date = ? AND type = 'Text' AND pages <= ? AND lang='fre' " );

if ( $from < 1610 )
  $qtot = $db->prepare( "SELECT count(*) AS count FROM document WHERE date = ? AND type = 'Text' AND (lang='fre' OR lang='frm') " );
else
  $qtot = $db->prepare( "SELECT count(*) AS count FROM document WHERE date = ? AND type = 'Text'  AND lang='fre' " );

// $qtext = $db->prepare( "SELECT count(*) AS count FROM document WHERE date = ? AND type = 'Text' " );


if ( $from < 1610 )
  $qpages = $db->prepare( "SELECT avg(pages) FROM document WHERE date = ?  AND type = 'Text' AND (lang='fre'  OR lang='frm')" );
else
  $qpages = $db->prepare( "SELECT avg(pages) FROM document WHERE date = ?  AND type = 'Text' AND lang='fre'" );

/*
if ( $from < 1610 )
  $qpages2 = $db->prepare( "SELECT sum(pages) FROM document WHERE date = ?  AND type = 'Text' " );
else
  $qpages2 = $db->prepare( "SELECT sum(pages) FROM document WHERE date = ?  AND type = 'Text' " );
*/

$lastpages = 0;
for ( $date=$from; $date <= $to; $date++ ) {
  $qbook->execute( array( $date, $pagefloor ) );
  list( $book ) = $qbook->fetch( PDO::FETCH_NUM );
  $qbroch->execute( array( $date, $pagefloor ) );
  list( $broch ) = $qbroch->fetch( PDO::FETCH_NUM );
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
  echo "[".$date.", ".$book;
    // .", ".number_format( (100*$nop/$tot), 2, '.', '')
  // echo  ", ".$text;
  echo  ", ".$broch;
  echo  ", ".$tot;
  // echo ", ".number_format( $pages, 2, '.', '');
  // echo  ", ".$pages2;
  echo "],\n";
}
// nom de colonnes
$A = "Titres > $pagefloor p.";
$B = "Titres <= $pagefloor p.";
       ?>],
      {
        labels: [ "Année", "<?=$A?>", "<?=$B?>", "Titres en français" ], // "Moy. pages",
        legend: "always",
        labelsSeparateLines: "true",
        ylabel: "Titres",
        y2label: "Pages",
        showRoller: true,
        <?php if ($log) echo "logscale: true,";  ?>
        rollPeriod: <?php echo $smooth ?>,
        series: {
          "<?=$A?>": {
            drawPoints: true,
            pointSize: 3,
            color: "rgba( 255, 0, 0, 0.5 )",
            strokeWidth: 1,
            fillGraph: true,
          },
          "<?=$B?>": {
            drawPoints: false,
            pointSize: 3,
            color: "rgba( 0, 0, 255, 0.2 )",
            strokeWidth: 0,
            fillGraph: true,
          },
          "Titres en français": {
            drawPoints: false,
            pointSize: 0,
            color: "rgba( 0, 0, 0, 1 )",
            strokeWidth: 2,
          },
          "Moy. pages": {
            axis: 'y2',
            color: "rgba( 128, 128, 128, 0.5)",
            strokeWidth: 3,
            strokePattern: [4,4],
          },
        },
        axes: {
          x: {
            gridLineWidth: 1,
            gridLineColor: "rgba( 0, 0, 0, 0.2)",
            drawGrid: false,
            independentTicks: true,
          },
          y: {
            independentTicks: true,
            drawGrid: true,
            axisLabelColor: "rgba( 255, 0, 0, 0.9)",
            gridLineColor: "rgba( 255, 0, 0, 0.2)",
            gridLineWidth: 1,
          },
          y2: {
            independentTicks: true,
            drawGrid: true,
            gridLineColor: "rgba( 128, 128, 128, 0.1)",
            gridLineWidth: 3,
            // gridLinePattern: [4,4],
          },
        },
        underlayCallback: function(canvas, area, g) {
          canvas.fillStyle = "rgba(255, 128, 0, 0.2)";
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
        { series: "Titres en français", x: "1562", shortText: "Guerres de Religion", width: "", height: "", cssClass: "ann", },
        { series: "Titres en français", x: "1648", shortText: "La Fronde", width: "", height: "", cssClass: "ann", },
        { series: "Titres en français", x: "1789", shortText: "1789", width: "", height: "", cssClass: "ann", },
        { series: "Titres en français", x: "1815", shortText: "1815", width: "", height: "", cssClass: "ann", },
        { series: "Titres en français", x: "1830", shortText: "1830", width: "", height: "", cssClass: "ann", },
        { series: "Titres en français", x: "1848", shortText: "1848", width: "", height: "", cssClass: "ann", },
        { series: "Titres en français", x: "1870", shortText: "1870", width: "", height: "", cssClass: "ann", },
        { series: "Titres en français", x: "1914", shortText: "1914", width: "", height: "", cssClass: "ann", },
        { series: "Titres en français", x: "1939", shortText: "1939", width: "", height: "", cssClass: "ann", },
        { series: "Titres en français", x: "1968", shortText: "1968", width: "", height: "", cssClass: "ann", },
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
    <p>
Le nombre de titres français par an augmente beaucoup au cours des siècles,
de quelques titres au début de la bibliothèque (1537), à plusieurs dizaines de milliers de nos jours.
Les effectifs sont à observer en tendance, car ils sont affectés par différents taux d’erreur qui se combinent.
7% des notices sont sans date, 11% n’ont pas de langue, le taux de documents sans nombre de pages (repérable automatiquement)
est variable.
Ce décalage est observable en comparant le nombre global de documents datés en français et ceux de <a href="?pagefloor=0&amp;from=1905&amp;to=1970">plus de 0 pages</a>.
Le différentiel est rempli de titres sans description physique, sans nombre de pages dans la description,
ou bien pour le XIX<sup>e</sup> et le XX<sup>e</sup> s., un nombre important de tirés à part.
La variation du nombre moyen de pages reste cependant très significative des événements historiques (guerres, révolutions…),
affectant une édition très concentrée à <a href="paris.php">Paris</a>.
On remarque par exemple que l’agitation de la Fronde, de 1789 ou de 1848 produit beaucoup de titres de peu de pages (brochures)
mais affecte beaucoup moins les nombre de titres de plus de 100 pages.
Les guerres, par contre, affectent durement tous les genres éditoriaux (restrictions de papier, blocage de Paris).
  </p>
  <?php include ( dirname(__FILE__).'/footer.php' ) ?>
  </body>
</html>
