<?php
$from = 1600;
$to = 1850;
$smooth = 2;
include ( dirname(__FILE__).'/Cataviz.php' );
$db = new Cataviz( "databnf.sqlite" );

?><!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <title>Antiquité, Databnf.</title>
    <script src="lib/dygraph.min.js">//</script>
    <link rel="stylesheet" type="text/css" href="lib/dygraph.css"/>
    <link rel="stylesheet" type="text/css" href="cataviz.css"/>
    <style>
.dygraph-legend { left: 40% !important; top: 1ex !important; }
.ann { transform: rotateZ(-90deg); transform-origin: 0% 100%; padding-left: 1em; border-left: none !important; border-bottom: 1px solid #000 !important; font-size: 16pt !important; font-weight: bold; color: rgba( 0, 0, 0, 0.4) !important; }
.ann2 { transform: rotateZ(90deg); transform-origin: 0% 0%; padding: 0 1em; border: none !important; border-top: 1px solid #000 !important; font-size: 16pt !important; font-weight: bold; color: rgba( 0, 0, 0, 0.7) !important; background: rgba( 255, 255, 255, 0.1); }
    </style>
  </head>
  <body>
    <?php include ( dirname(__FILE__).'/menu.php' ) ?>
    <header>
      <div class="links">
          <a href="?">Langues anciennes</a>
        | <a href="?from=1485&amp;to=1650&amp;smooth=5">Renaissance</a>
        | <a href="?from=1600&amp;to=1840&amp;smooth=5">Lumières</a>
        | <a href="?from=1789&amp;to=1918&amp;smooth=5">XIX<sup>e</sup> s.</a>
        | <a href="?from=1914&amp;to=2020">XX<sup>e</sup> s.</a>
      </div>
      <form name="dates">
        <button onclick="window.location.href='?'; " type="button">Reset</button>
        From <input name="from" size="4" value="<?php echo $from ?>"/>
        to <input name="to" size="4" value="<?php echo  $to ?>"/>
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
$sigma = 0;
if ( $from < 1800 ) $sigma = 2;

// fre, eng, ger, ita, zxx ?, spa, lat, frm, ara, gre, chi
// part des documents avec un langue
$qgrc = $db->prepare( "SELECT count(*) AS count FROM document WHERE type = 'Text' AND lang = 'grc' AND date = ? " );
$qlatant = $db->prepare( "SELECT count(*) AS count FROM document WHERE type = 'Text' AND  lang = 'lat' AND birthyear < 150 AND date = ?" );
$qlatmed = $db->prepare( "SELECT count(*) AS count FROM document WHERE type = 'Text' AND  lang = 'lat' AND birthyear >= 150 AND birthyear < 1450 AND date = ? " );
$qlatmod = $db->prepare( "SELECT count(*) AS count FROM document WHERE type = 'Text' AND  lang = 'lat' AND birthyear >= 1450 AND date = ?" );
$qlatmisc = $db->prepare( "SELECT count(*) AS count FROM document WHERE type = 'Text' AND  lang = 'lat' AND birthyear IS NULL AND date = ?" );
$qtrad = $db->prepare( "SELECT count(*) AS count FROM document WHERE  type = 'Text' AND (lang = 'frm' OR lang = 'fre') AND birthyear < 1400 AND date = ?" );
// il y a des images, des partitions ou des disques en latin
$qmulti = $db->prepare( "SELECT count(*) AS count FROM document WHERE  (type = 'Score' OR type = 'Image' OR type = 'Sound' OR type = 'MovingImage' OR type='StillImage' ) AND (lang = 'lat' OR lang = 'grc') AND date = ?" );

$qtext = $db->prepare( "SELECT count(*) AS count FROM document WHERE  type = 'Text' AND date = ?" );
$qnolang = $db->prepare( "SELECT count(*) AS count FROM document WHERE  type = 'Text' AND lang IS NULL AND date = ?" );

$multi = $trad = $latmod = $latant = $latmed = $latmisc = $grc = $nolang = array();
// boucler sur les dates et stocker dans un tableau pour le smooth
for ( $date=$from; $date <= $to; $date++ ) {

  $qmulti->execute( array( $date ) );
  $multi[$date] = current( $qmulti->fetch( PDO::FETCH_NUM ) ) ;

  $qtrad->execute( array( $date ) );
  $trad[$date] = current( $qtrad->fetch( PDO::FETCH_NUM ) ) ;

  $qlatmod->execute( array( $date ) );
  $latmod[$date] = current( $qlatmod->fetch( PDO::FETCH_NUM ) ) ;

  $qlatant->execute( array( $date ) );
  $latant[$date] = current( $qlatant->fetch( PDO::FETCH_NUM ) ) ;

  $qlatmed->execute( array( $date ) );
  $latmed[$date] = current( $qlatmed->fetch( PDO::FETCH_NUM ) ) ;

  $qlatmisc->execute( array( $date ) );
  $latmisc[$date] = current( $qlatmisc->fetch( PDO::FETCH_NUM ) ) ;

  $qgrc->execute( array( $date ) );
  $grc[$date] = current( $qgrc->fetch( PDO::FETCH_NUM ) ) ;

  $qtext->execute( array( $date ) );
  $qnolang->execute( array( $date ) );
  $nolang[$date] = 100.0*current( $qnolang->fetch( PDO::FETCH_NUM ) )/ current( $qtext->fetch( PDO::FETCH_NUM ) );

}
// sortir le tableau de résultats
for ( $date=$from; $date <= $to; $date++ ) {
  echo "[".$date
    .", ".$multi[$date]
    .", ".$trad[$date]
    .", ".$latmod[$date]
    .", ".$latant[$date]
    .", ".$latmed[$date]
    .", ".$latmisc[$date]
    .", ".$grc[$date]
    .", ".$nolang[$date]
  ."],\n";

}
       ?>],
      {
        labels: [ "Année", "Multimédia", "Traductions", "Latin moderne", "Latin ancien", "Latin médiéval", "Autres latins", "Grec ancien", "% sans langue" ],
        ylabel: "Nombre de titres",
        labelsSeparateLines: "true",
        showRoller: true,
        rollPeriod: <?php echo $smooth ?>,
        legend: "always",
        strokeWidth: 5,
        logscale: <?php echo $log ?>,
        series: {
          "Multimédia": {
            strokeWidth: 3,
            color: "rgba( 0, 128, 0, 0.5)",
          },
          "Traductions": {
            strokeWidth: 3,
            color: "rgba( 0, 0, 0, 0.7)",
          },
          "Latin moderne": {
            strokeWidth: 3,
            color: "rgba( 255, 64, 0, 0.5)",
            // strokePattern: [5, 5],
          },
          "Latin médiéval": {
            strokeWidth: 3,
            color: "rgba( 255, 0, 128, 0.5)",
            // strokePattern: [5, 5],
          },
          "Latin ancien": {
            strokeWidth: 3,
            color: "rgba( 255, 0, 0, 0.7)",
          },
          "Autres latins" : {
            strokeWidth: 3,
            color: "rgba( 128, 0, 0, 0.6)",

          },
          "Grec ancien": {
            strokeWidth: 3,
            color: "rgba( 0, 128, 255, 0.8)",
          },
          "% sans langue": {
            axis: 'y2',
            color: "rgba( 160, 160, 160, 1)",
            strokeWidth: 1,
            fillGraph: true,
          }
        },
        axes: {
          x: {
            drawGrid: false,
            independentTicks: true,
          },
          y: {
            // includeZero: true,
          },
          y2: {
            valueRange:[1,31],
            independentTicks: true,
            drawGrid: true,
            gridLineColor: "rgba( 192, 128, 160, 0.5)",
            gridLineWidth: 1,
          },
        },
        underlayCallback: function(canvas, area, g) {
          canvas.fillStyle = "rgba(192, 192, 192, 0.3)";
          var periods = [ [1789,1794], [1814,1815], [1830,1831], [1848,1849], [1870,1871], [1914,1919], [1939,1945]];
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
        { series: "Grec ancien", x: "1789", shortText: "1789", width: "", height: "", cssClass: "ann", },
        { series: "Grec ancien", x: "1815", shortText: "1815", width: "", height: "", cssClass: "ann", },
        { series: "Grec ancien", x: "1830", shortText: "1830", width: "", height: "", cssClass: "ann", },
        { series: "Grec ancien", x: "1848", shortText: "1848", width: "", height: "", cssClass: "ann", },
        { series: "Grec ancien", x: "1870", shortText: "1870", width: "", height: "", cssClass: "ann", },
        { series: "Grec ancien", x: "1914", shortText: "1914", width: "", height: "", cssClass: "ann", },
        { series: "Grec ancien", x: "1939", shortText: "1939", width: "", height: "", cssClass: "ann", },
        { series: "% sans langue", x: "1972", shortText: "1972", width: "", height: "", cssClass: "ann2", },
      ]);
    });
    var linear = document.getElementById("linear");
    var log = document.getElementById("log");
    if ( linear && log ) {
      var setLog = function(val) {
        g.updateOptions({ logscale: val });
        linear.disabled = !val;
        log.disabled = val;
      };
      linear.onclick = function() { setLog(false); };
      log.onclick = function() { setLog(true); };
    }

    </script>
    <div class="text">
    <p>
    Un document du catalogue a généralement une langue, sauf 11,1 % des textes, et 20,8 % pour les autres types de documents, comme images, cartes, partitions… Les documents multimédias associés à une langue ancienne peuvent être significatifs à certaines époques, comme de nos jours avec les disques et les films.
    Les textes sans langue déclarée ne peuvent être résolus automatiquement, il est par exemple imprudent d’inférer une langue par les mots du titre, beaucoup d’éditions grecques sont par exemple titrées en latin, en allemand, ou en anglais. La part des titres sans langue varie beaucoup selon les années, jusqu’à 30 % en 1972, il faut donc tenir compte avant d’oser une interprétation. Beaucoup de documents peuvent être rapportés à un auteur personne, ce champ est très fiable. En ce cas, les documents peuvent être rapportés à la date de naissance de l’auteur principal, ce qui permet de distinguer plusieurs prériodes historiques. Cette date permet alors de découvrir les traduction en français de textes anciens. Toutefois, l’activité éditoriale comporte beaucoup de textes latin sans auteurs personnel, comme la bible, ou des traités de droits canon, qui sont donc difficiles à dater.
    Il en résulte les catégories suivantes :</p>
    <ul>
      <li><b>Multimédia</b> : images, partititions, cartes, disques films… associés au latin ou au grec ancien.</li>
      <li><b>Traductions</b> : textes français à partir d’auteurs nés avant 1400.</li>
      <li><b>Latin moderne</b> : textes latins d’auteurs nés après 1400, généralement religieux ou savant.</li>
      <li><b>Latin médiéval</b> : textes latins religieux, des pères de l’église à 1400.</li>
      <li><b>Latin antique</b> : textes latins d’un auteur né avnt +150.</li>
      <li><b>Grec ancien</b> : textes en grec ancien.</li>
    </ul>
    <p>On découvrira que le latin est longtemps resté une langue très vivante, du moins, par le nombre de titres, même durant la <a href="?from=1485&amp;to=1650&amp;smooth=5">Renaissance</a>. La particularité européenne de l’Université française, dans la capitale parisienne, explique peut-être le déclin de l’édition latine durant les <a href="?from=1600&amp;to=1840&amp;smooth=5">Lumières</a>, achevées par l’Empire et sa réforme de l’enseignement supérieur. Par comparaison, le <a href="?from=1789&to=1918&smooth=5">XIX<sup>e</sup></a> semble un renouveau pour les latins religieux et savant, mais les éditions en langues anciennes sont supplantées par les traductions en frnaçais vers 1830. Au <a href="?from=1914&to=2020">XX<sup>e</sup></a> siècle, la traduction des classiques continue à progresser, les études grecques reviennent, même la latin d’église profite des progrès techniques de l’imprimerie pour diffuser plus. Si la part des langues anciennes est désormais marginale, le nombre de titres, et donc leur disponibilité, n’a jamais été aussi bonne.</p>
    </div>
    <?php include ( dirname(__FILE__).'/footer.php' ) ?>
  </body>
</html>
