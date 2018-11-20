<?php
$from = 1760;
$to = 1960;
$datemax = 2015;
include (dirname(__FILE__).'/Cataviz.php');
$db = new Cataviz("databnf.sqlite");

?><!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <title>Lieux de mort, Databnf.</title>
    <script src="lib/dygraph.min.js">//</script>
    <link rel="stylesheet" type="text/css" href="lib/dygraph.css"/>
    <link rel="stylesheet" type="text/css" href="cataviz.css"/>
    <style>
    .dygraph-legend { left: 7ex !important; width: 25ex; top: 40px !important; }
/*
.dygraph-ylabel { color: rgba(192, 0, 0, 1); font-weight: normal; }
.dygraph-y2label { color: rgba(128, 128, 128, 0.5); }
*/
    </style>
  </head>
  <body>
    <?php include (dirname(__FILE__).'/menu.php') ?>
    <header>
      <div class="links">
          <a href="?">Lieux de mort</a>
        | <a href="?from=1760&amp;to=1860">Révolutions</a> 
        | <a href="?from=1900&amp;to=1960">Guerres mondiales</a> 
      </div>
      <form name="dates">
        De <input name="from" size="4" value="<?php echo $from ?>"/>
        à <input name="to" size="4" value="<?php echo  $to ?>"/>
        <button type="submit">▶</button>
        <button onclick="window.location.href='?'; " type="button">Reset</button>
      </form>
    </header>
    <div id="chart" class="dygraph"></div>
    <script type="text/javascript">
var data = [
<?php

// pas de moyenne pour ces dates
$guerres = [ 1788, 1789, 1790, 1791, 1792, 1793, 1794,  1869, 1870, 1871, 1872, 1914, 1915, 1916, 1917, 1918, 1939, 1940, 1941, 1942, 1943, 1944, 1945, 1946 ];
$guerres = array_flip($guerres);


$qf = $db->prepare("SELECT count(*) AS count, avg(age) AS age, deathparis FROM person WHERE fr = 1 AND gender = 2 AND deathyear >= ? AND deathyear <= ? GROUP BY deathparis ORDER BY deathparis");
$qm = $db->prepare("SELECT count(*) AS count, avg(age) AS age, deathparis FROM person WHERE fr = 1 AND gender = 1 AND deathyear >= ? AND deathyear <= ? GROUP BY deathparis ORDER BY deathparis");
$qm50 = $db->prepare("SELECT count(*) AS count, deathparis FROM person WHERE fr = 1 AND gender = 1 AND age <= 50 AND deathyear >= ? AND deathyear <= ? GROUP BY deathparis ORDER BY deathparis");


for ($date=$from; $date <= $to; $date++) {

  echo "  [".$date;

  $f=$m=$m50=array(null=>array(0, 0), 0=>array(0, 0), 1=>array(0, 0));

  $delta = 10;
  if ($date > 1600) $delta = 8;
  if ($date > 1700) $delta = 6;
  if ($date > 1789) $delta = 4;
  if ($date >= 1900) $delta = 4;
  if (isset($guerres[$date]) && $date > 1900) $delta = 2;

  $qf->execute(array($date-$delta, $date+$delta));
  // charger les chiffres, des rangs vides peuvent manquer
  while ($row = $qf->fetch(PDO::FETCH_NUM)) {
    $f[$row[2]] = $row;
  }

  if (!$f[1][0]) echo ",0,,";
  else {
    // ♀ Paris/Ailleurs %
    echo ",". number_format(100.0 * $f[1][0] / ($f[0][0]+$f[1][0]), 2, '.', '');
    // ♀ âge Paris
    echo ",". number_format($f[1][1], 2, '.', '');
    // ♀ âge Ailleurs
    echo ",". number_format($f[0][1], 2, '.', '');
  }
  // ♀ erreur
  // echo ",". @number_format(100.0 * $f[null][0] / ($f[null][0]+$f[0][0]+$f[1][0]), 2, '.', '');


  $delta = 6;
  if ($date >= 1600) $delta = 5;
  if ($date >= 1700) $delta = 4;
  if ($date >= 1789) $delta = 3;
  if ($date >= 1860) $delta = 2;
  if ($date >= 1919) $delta = 1;
  if (isset($guerres[$date])) $delta = 1;
  $qm->execute(array($date-$delta, $date+$delta));
  while ($row = $qm->fetch(PDO::FETCH_NUM)) {
    $m[$row[2]] = $row;
  }

  /*
  if ($date >= 1600) $delta = 10;
  if ($date >= 1700) $delta = 5;
  if ($date >= 1789) $delta = 3;
  if ($date >= 1860) $delta = 3;
  if ($date >= 1919) $delta = 4;
  if (isset($guerres[$date])) $delta = 1;
  $qm50->execute(array($date-$delta, $date+$delta));
  while ($row = $qm50->fetch(PDO::FETCH_NUM)) {
    $m50[$row[1]] = $row;
  }
  */

  if (!$m[1][0]) {
    echo ",0,,";
  }
  else {
    // ♂ Paris/Ailleurs %
    echo ",". number_format(100.0 * $m[1][0] / ($m[0][0]+$m[1][0]), 2, '.', '');
    // ♂ < 50 Paris/Ailleurs %
    // echo ",". number_format(100.0 * $m50[1][0] / ($m50[0][0]+$m50[1][0]), 2, '.', '');
    // ♂ âge Paris
    echo ",". number_format($m[1][1], 2, '.', '');
    // ♂ âge Ailleurs
    echo ",". number_format($m[0][1], 2, '.', '');
  }
  // ♂ erreur
  // echo ",". @number_format(100.0 * $m[null][0] / ($m[null][0]+$m[0][0]+$m[1][0]), 2, '.', '');


  echo "],\n";
}?>
];
var attrs = {
  title : "Databnf, âge et lieu de mort (Paris/Ailleurs), à la date de mort.",
  labels: [ "Année",
    "♀ % Paris", "♀ Âge mort à Paris", "♀ Âge mort Ailleurs", // "♀ % ???",
    "♂ % Paris", "♂ Âge mort à Paris", "♂ Âge mort Ailleurs", // "♂ % ???",
  ],
  ylabel: "% auteurs morts à Paris",
  y2label: "Âge à la mort",
  series: {
    "♀ % Paris": {
      color: "rgba(255, 160, 160, 1)",
      fillGraph: true,
      strokeWidth: 0.5,
    },
    "♂ % Paris": {
      color: "rgba(160, 160, 255, 1)",
      fillGraph: true,
      strokeWidth: 0.5,
    },
    "♀ Âge mort à Paris" : {
      color: "rgba(255, 0, 0, 0.3)",
      strokeWidth: 4,
      axis: 'y2',
    },
    "♂ Âge mort à Paris" : {
      color: "rgba(0, 0, 128, 0.6)",
      strokeWidth: 4,
      axis: 'y2',
    },
    "♀ Âge mort Ailleurs" : {
      color: "rgba(255, 0, 0, 0.3)",
      strokeWidth: 4,
      strokePattern: [4,4],
      axis: 'y2',
    },
    "♂ Âge mort Ailleurs" : {
      color: "rgba(0, 0, 128, 0.6)",
      strokeWidth: 4,
      strokePattern: [4,4],
      axis: 'y2',
    },
  },
};
var annoteSeries = "♂ % Paris";
<?php include('dygraph-common.php') ?>
    </script>
    <div class="text">
      <p>30 à 50% des notices d’auteur personne peuvent comporter un lieu de mort. Le taux de renseignement varie surtout selon l’époque (1500–1600 : 31 %, 1600–1700 : 39 %, 1700–1800 : 45 %, 1800–1950 : 53 %, 1950–2015 : 37 %). La baisse actuelle s’explique par l’augmentation du nombre de titres, avec des auteurs de moins en moins connus. La variation du taux de lieux inconnus n'est pas corrélée à d'autres critères. Le lieu de mort le plus fréquent est Paris. Le rapport Paris/Ailleurs n’est donc pas troublé par les pratiques documentaire. Attention, le nombre de femmes étant plus faible (1937, femmes : 9, hommes : 389), les variations à court terme peuvent être accidentées. Une moyenne glissante est appliquée selon l’année, exagérant l’inertie autour des guerres. Les lignes donnent des âges à la mort, les zones de couleurs indiquent la proportion Paris/Ailleurs.</p>
      <p>Les deux <a href="?from=1900&amp;to=1960">guerres mondiales</a> affectent les auteurs de manière différente. En 1914–1918, plus d’hommes meurent ailleurs qu’à Paris, leur longévité baisse, tandis que celle de Paris se maintient. Les morts les plus jeunes finissent massivement leurs jours dans les départements du front : Pas-de-Calais, Nord, Somme, Meuse… Les retours se font vite. La seconde guerre mondiale frappe partout, à Paris, et encore plus fort ailleurs. La liste des lieux pour les gens jeunes et sans équivoque, camps allemands ou prisons françaises. Ensuite, le retour à Paris est moins prononcé qu’en 1918, les morts intervenant généralement dans les provinces françaises. L’Évacuation et les privations de la guerre en ville ont durablement rapproché les écrivains des campagnes.</p>
      <p>La <a href="?from=1770&amp;to=1850">Révolution</a> montre d’autres phénomènes. 1789 élève rapidement le nombre d’auteurs venus mourir à Paris, alors qu’il n’y a pas encore de hausse de la mortalité (âge à la mort). La Terreur baisse la longévité, plus à Paris qu’ailleurs. Ensuite, la part des morts à Paris diminue, ce qui laisse penser à des départ massifs. Le retour des hommes se fera très progressivement à partir de 1800. En regard, le nombre des femmes restant à Paris semble s’élever.</p>
    </div>
    <?php include (dirname(__FILE__).'/footer.php') ?>
  </body>
</html>
