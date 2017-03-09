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
if ( $to < 1475 ) $to = 2014;
if ( $to > 2014 ) $to = 2014;

if ( isset($_REQUEST['smooth']) ) $smooth = $_REQUEST['smooth'];
else $smooth = 4;
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
.dygraph-ylabel { color: rgba( 192, 0, 0, 1 ); font-weight: normal; }
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
        <button type="submit">▶</button>
      </form>
    </header>
    <div id="chart" class="dygraph" style="width:100%; height:600px;"></div>
    <script type="text/javascript">
    g = new Dygraph(
      document.getElementById("chart"),
      [
<?php
$qtitf = $db->prepare( "SELECT count(*) AS count FROM document WHERE document.date = ? AND gender = 2 AND type='Text' AND lang='fre' " );
$qtit = $db->prepare( "SELECT count(*) AS count FROM document WHERE document.date = ? AND pers = 1 AND type='Text' AND lang='fre' " );
// logique un peu bizarre, mais permet de profiter de l’index birthyear au max, gens entre 20 et 70 ans (mais pas morts)
// après expérience, pas très intéressant
// $qautf = $db->prepare( "SELECT count(*) FROM person WHERE gender = 2 AND writes = 1 AND lang = 'fre' AND birthyear <= (? - 20) AND birthyear >= (?-70) AND deathyear > ? " );
$qpagesf = $db->prepare( "SELECT avg(pages) FROM document WHERE date = ?  AND type = 'Text' AND lang='fre' AND gender=2 " );
$qpages = $db->prepare( "SELECT avg(pages) FROM document WHERE date = ?  AND type = 'Text' AND lang='fre' " );


$titf = 0;
$tit = 0;
$pagesf = 0;
$pages = 0;
for ( $date=$from; $date <= $to; $date++ ) {
  $qtitf->execute( array( $date ) );
  list( $titf ) = $qtitf->fetch( PDO::FETCH_NUM );
  $qtit->execute( array( $date ) );
  list( $tit ) = $qtit->fetch( PDO::FETCH_NUM );
  $qpagesf->execute( array( $date ) );
  list( $pagesf ) = $qpagesf->fetch( PDO::FETCH_NUM );
  $qpages->execute( array( $date ) );
  list( $pages ) = $qpages->fetch( PDO::FETCH_NUM );
  echo "[".$date;
  echo ",".number_format( 100*($titf/$tit), 2, '.', '');
  echo ",".$pagesf;
  echo ",".$pages;
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
        labels: [ "Année", "Femmes % titres", "Femmes, pages", "Pages" ],
        legend: "always",
        labelsSeparateLines: "true",
        ylabel: "Part des titres %",
        y2label: "Nombre moyen de pages",
        showRoller: true,
        rollPeriod: <?php echo $smooth ?>,
        series: {
          "Femmes % titres": {
            // drawPoints: true,
            // pointSize: 3,
            color: "rgba( 192, 0, 0, 1 )",
            strokeWidth: 2,
          },
          "Femmes, pages": {
            axis: 'y2',
            color: "rgba( 255, 0, 0, 0.3 )",
            strokeWidth: 5,
          },
          "Pages": {
            axis: 'y2',
            color: "rgba( 128, 128, 128, 0.2)",
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
        { series: "Femmes % titres", x: "1648", shortText: "La Fronde", width: "", height: "", cssClass: "ann", },
        { series: "Femmes % titres", x: "1789", shortText: "1789", width: "", height: "", cssClass: "ann", },
        { series: "Femmes % titres", x: "1815", shortText: "1815", width: "", height: "", cssClass: "ann", },
        { series: "Femmes % titres", x: "1830", shortText: "1830", width: "", height: "", cssClass: "ann", },
        { series: "Femmes % titres", x: "1848", shortText: "1848", width: "", height: "", cssClass: "ann", },
        { series: "Femmes % titres", x: "1870", shortText: "1870", width: "", height: "", cssClass: "ann", },
        { series: "Femmes % titres", x: "1914", shortText: "1914", width: "", height: "", cssClass: "ann", },
        { series: "Femmes % titres", x: "1939", shortText: "1939", width: "", height: "", cssClass: "ann", },
        { series: "Femmes % titres", x: "1968", shortText: "1968", width: "", height: "", cssClass: "ann", },
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
    <p>Par an, la part des titres en français signés par une femme est très basse jusqu’au XX<sup>e</sup> s., &lt; 5%. On observe une progression sur le temps long, pour atteindre 1/3  de nos jours. La proportion de titres féminins baisse pendant les guerres et les révolutions, montrant bien qu’en période de restriction de papier, les hommes passent avant. Les titres féminins ont un nombre moyen de pages supérieur aux titres masculins, affecté pas des variations historiques comparables. Il est difficile d’inférer un genre avec juste le catalogue, mais on peut supposer que plus rares, les femmes écrivent des ouvrages plus importants, et ne remplissent pas la grande masse de petits fascicules utilitaires. Ce mouvement a changé. Depuis 1940, le nombre moyen de pages est comparable entre les sexes, et suit le mouvement historique, avec notamment une augmentation du nombre moyen de pages jusque 1968 (parallèle à une explosion du nombre de titres).  Après ce pic, la généralisation de l’offset et de la quadrichromie modifie la nature des imprimés déposés. Le nombre de pages baisse, les femmes ont devancé ce mouvement, et se retrouvent maintenant avec un nombre de pages moyen plus bas. Doit-on incriminer l’explosion de la littérature pour enfants et des livres de cuisine ?
    </p>
    <?php include ( dirname(__FILE__).'/footer.php' ) ?>
  </body>
</html>
