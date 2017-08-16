<?php
$from = 1770;
$to = 1970;
include ( dirname(__FILE__).'/Cataviz.php' );
$db = new Cataviz( "databnf.sqlite" );
$gender = @$_REQUEST['gender'];
if ( $gender != 1 && $gender != 2 ) $gender = null;
if ( $gender == 2 && $from < 1700 ) $smooth = 0;
else if ( $gender == 2 && $from < 1850 ) $smooth = 2;
else if ( $from < 1700 ) $smooth = 2;

?><!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <title>Générations, Databnf</title>
    <script src="lib/dygraph.min.js">//</script>
    <link rel="stylesheet" type="text/css" href="lib/dygraph.css"/>
    <link rel="stylesheet" type="text/css" href="cataviz.css"/>
    <style>
    .dygraph-legend { left: 65px !important; top: 40px !important; }
    /*
    .ann { transform: rotateZ(45deg); transform-origin: 10% 50%; padding-left: 1em; border-left: none !important; border-bottom: 1px solid #000 !important; font-size: 14pt !important; font-weight: normal; }
    */
    </style>
  </head>
  <body>
    <?php include ( dirname(__FILE__).'/menu.php' ) ?>
    <header>
      <div class="links">
        <a href="?">Générations</a>
        | <a href="?from=1770&amp;to=1970">2 siècles</a> 
        | <a href="?from=1770&amp;to=1850&amp;gender=2">♀ révolutions</a> 
        | <a href="?from=1860&amp;to=1970&amp;gender=2">♀ guerres mondiales</a> 
      </div>
      <form name="dates">
        <button onclick="window.location.href='?'; " type="button">Reset</button>
        De <input name="from" size="4" value="<?php echo $from ?>"/>
        à <input name="to" size="4" value="<?php echo  $to ?>"/>
        Sexe <select name="gender" onchange="this.form.submit()">
          <option value=""/>
          <option value="2" <?php if ($gender==2) echo ' selected="selected"' ?>>Femmes</option>
          <option value="1" <?php if ($gender==1) echo ' selected="selected"' ?>>Hommes</option>
        </select>
        <button type="submit">▶</button>
      </form>
    </header>
    <div id="chart" class="dygraph"></div>
    <script type="text/javascript">
    g = new Dygraph(
      document.getElementById("chart"),
      [
<?php
$colors = array( "rgba( 255, 0, 0, 1)", "rgba( 128, 0, 128, 1)", "rgba( 0, 0, 128, 1)", "rgba( 0, 128, 128, 1)", "rgba( 0, 128, 0, 1)", "rgba( 192, 192, 0, 1)", "rgba( 255, 128, 0, 1)" );

  $guerres = [ 1789, 1790, 1791, 1792, 1793, 1794, 1870, 1871, 1914, 1915, 1916, 1917, 1918, 1939, 1940, 1941, 1942, 1943, 1944, 1945, 1946 ];
$guerres = array_flip( $guerres );

// prendre les décennies
if ( $gender ) {
  $decq = $db->prepare( "SELECT birthdec, COUNT(*) FROM document WHERE book = 1 AND lang = 'fre' AND posthum=0 AND gender = ? AND date >= ? AND date <= ? GROUP BY birthdec  ORDER BY birthdec");
  $decq->execute( array( $gender, $from, $to ) );
}
else {
  $decq = $db->prepare( "SELECT birthdec, COUNT(*) FROM document WHERE book = 1 AND lang = 'fre' AND posthum=0 AND date >= ? AND date <= ? GROUP BY birthdec  ORDER BY birthdec");
  $decq->execute( array( $from, $to ) );
}

$dec = array();
while ($row = $decq->fetch( PDO::FETCH_NUM )) {
  if ( $row[0] == null ) continue;
  $dec[$row[0]] = 0;
}
$dec = array_reverse( $dec, true );

$gq = $db->prepare( "SELECT gender, COUNT(*) AS count FROM document WHERE book = 1 AND lang = 'fre' AND posthum=0 AND date >= ? AND date <= ? GROUP BY gender " );

if ( $gender ) {
  $decq = $db->prepare( "SELECT birthdec, COUNT(*) AS count FROM document WHERE book = 1 AND lang = 'fre' AND posthum=0 AND gender = ? AND date = ? GROUP BY birthdec ORDER BY birthdec" );
}
else {
  $decq = $db->prepare( "SELECT birthdec, COUNT(*) AS count FROM document WHERE book = 1 AND lang = 'fre' AND posthum=0  AND date = ? GROUP BY birthdec ORDER BY birthdec" );
}

for ( $date=$from; $date <= $to; $date++ ) {
  echo "[".$date;
  $f = $m = $o = 0;
  $delta = 2;
  if ( $date > 1800 ) $delta = 1;
  if ( $date > 1900 ) $delta = 1;
  if ( isset( $guerres[$date] )) $delta = 0;

  $gq->execute( array( $date-$delta, $date+$delta ) );
  while ($row = $gq->fetch( PDO::FETCH_NUM ) ) {
    if ( $row[0] === null ) $o = $row[1];
    else if ( $row[0] == 1 ) $m = $row[1];
    else if ( $row[0] == 2 ) $f = $row[1];
  }
  echo ", ". number_format( 100.0*$f  / ($f+$m), 2, '.', '');


  // recopier une table de décennies vierge
  $res = $dec;
  if ( $gender ) $decq->execute( array( $gender, $date ) );
  else  $decq->execute( array( $date ) );
  // charger la table de décennies
  while ($row = $decq->fetch( PDO::FETCH_NUM ) ) {
    if ( $row[0] === null ) continue;
    $res[$row[0]] = $row[1];
  }
  // afficher la table de décennies
  foreach ($res as $key => $value ) {
    echo ', '.$value;
  }

  // echo ",". @number_format( 100.0 * $f[1][1] / ( $f[0][1]+$f[1][1] ), 2, '.', '');


  echo "],\n";
}
       ?>],
      {
        title : "Databnf<?php if( $gender == 1) { echo ", hommes"; } else if( $gender == 2) { echo ", femmes"; } ?>, décennie de naissance à la date de publication.",
        titleHeight: 35,
        labels: [ "Année", "Femmes, % livres", <?php
        foreach ($dec as $key => $value) {
          echo '"'.$key.'", ';
        }
        ?> ],
        // legend: "always",
        labelsSeparateLines: false,
        y2label: "Femmes, % livres",
        ylabel: "Nombre de livres",
        showRoller: true,
        rollPeriod: <?php echo $smooth ?>,
        <?php if ($log) echo "logscale: 'true',\n";  ?>
        stackedGraph: true,
        series: {
          "Femmes, % livres":{
            stackedGraph: false,
            axis: 'y2',
            color: "rgba( 0, 0, 0, 1 )",
            fillGraph: false,
            strokeWidth: 4,
            strokePattern: [4,4],
          },
          <?php
          $mod = count( $colors );
          foreach ($dec as $key => $value) {
            $col = ($key/10) % $mod; // assure la même couleur pour une décennie
            echo "\n".'"'.$key.'": { color:"'.$colors[$col].'", strokeWidth: 0.2 },';
          }
        ?>

        },
        axes: {
          x: {
            gridLineWidth: 1,
            drawGrid: false,
            independentTicks: true,
            gridLineColor: "rgba( 128, 128, 128, 0.5)",
          },
          y: {
            independentTicks: true,
            drawGrid: true,
            gridLineColor: "rgba( 128, 128, 128, 0.1)",
            gridLineWidth: 1,
            includeZero: false,
          },
          y2: {
            independentTicks: true,
            drawGrid: true,
            gridLinePattern: [4,4],
            gridLineColor: "rgba( 192, 192, 192, 0.3)",
            gridLineWidth: 4,
          },
        },
        underlayCallback: function(canvas, area, g) {
          canvas.fillStyle = "rgba(192, 192, 192, 0.3)";
          var periods = [ [1789,1794], [1830,1831], [1848,1849], [1870,1872], [1914,1918], [1939,1945]];
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
        <?php
        if ( $gender == 2 ) echo '{ series: "Femmes, % livres", x: "1879", shortText: "1879, lois enseignement", width: "", height: "", cssClass: "ann-45" }, '."\n";
        for ( $i=1500; $i<1970; $i=$i+10) {
          echo '{ series: "'.($i).'", x: "'.($i+35).'", shortText: "'.($i).'", width: "", height: "", cssClass: "ann45" },'."\n";
        }
         ?>
      ]);
    });
    var linear = document.getElementById("linear");
    var log = document.getElementById("log");
    if ( log && linear ) {
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
    <p>Chaque livre en français (document > 50 p.), lorsqu’il est attribué à une personne avec une date de naissance, peut être associé à la décennie de naissance de l’auteur principal. Cette projection ne considère que les publications d’auteurs vivants. Il en résulte une sorte de tranche géologique, où chaque génération prend sa mesure entre 25 et 35 ans, pour lentement s’éteindre au rythme des oubliés et des morts. La croûte est fendue de plusieurs failles, 1939–1945, 1914–1918, 1870…, autour desquelles se réarticulent les survivants et les nouvelles générations. La vue générale est largment occupée par les titres masculins (> 95 %). Le taux de livres signés par une femme réagit fortement aux événements historiques, les guerres, les révolutions, et même les crises économiques.</p>
    <p>Malgré une proportion de titres plus élevée qu’avant et après, le paysage des femmes de <a href="?from=1770&amp;to=1850&amp;gender=2">1789 à 1830</a> n’observe pas la molle pente des générations, mais des failles très dures. La faiblesse des effectifs explique probablement cette sensibilité à l’événement, et aux individus, si bien que chaque cas semble particulier. La génération 1750 est effacée pendant la Terreur mais peut revenir pendant l’Empire et la Restauration (50 ans). La génération 1760 est surtout représentée par madame de Staël (45 rééditions de son vivant). La génération 1770 trouve enfin à s’exprimer pendant la Restauration (45 ans),  avec notamment Pauline Guizot, la femme du ministre, mais s’efface dès 1825. La génération 1780 se résume pour beaucoup à Marceline Desbordes-Valmore, celle de 1790 semble vivace pendant la seconde Restauration, mais il s’agit surtout de la comtesse de Ségur. Sur des effectifs aussi faibles, l’enquête n’a pas besoin de statistiques, une recherche biblio-biographique suffirait à cerner le milieu et sa production.</p>
    <p>Un zoom sur les <a href="?from=1860&amp;to=1970&amp;gender=2">femmes entre 1860 et 1970</a> montre d’autres phénomènes. Il faut d’abord avoir en tête que le nombre de titres sur la période s’établit sur une technologie stable, autrement dit, les titres pris par une catégorie le sont à une autre, contrairement à la croissance de 1815 à 1860, ou de 1960 à 2008. Après la guerre de 1870, le nombre de titres signés d’une femme reste stable, alors qu’il ne cessent de monter pour les hommes (au détriment, notamment, des rééditions posthumes). Après 1918, les titres masculins continuent sur la lancée de leur croissance d’avant-guerre, mais rencontrent durement la crise de 1930, avec une reprise à partir de 1936. Les rééditions ne retrouveront plus leur étiage avant 1946. Par contre, la croissance des titres féminins se maintiendra pendant la crise, contre les hommes, et se poursuivra pendant la Reconstruction. Depuis 1914, les femmes ne sont plus la variable d’ajustement du marché. L’effet des lois sur l’enseignement à partir de 1879 se voit clairement sur les génération 1880 et 1890, qui n’abandonneront plus la palce à leurs cadettes.</p>
    </div>
    <?php include ( dirname(__FILE__).'/footer.php' ) ?>
  </body>
</html>
