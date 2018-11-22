<?php
$datemax = 2014;
$from = 1880;
$to = 2014;
include (dirname(__FILE__).'/Cataviz.php');
$db = new Cataviz("databnf.sqlite");
$base100 = floor (($from + ($to - $from)/2.0) / 10.0) * 10;
if (isset($_REQUEST['base100']) && $_REQUEST['base100'] >= $from && $_REQUEST['base100'] <= $to) $base100 = $_REQUEST['base100'];
$books = 1;
if (isset($_REQUEST['books']) && is_numeric($_REQUEST['books'])) $books = $_REQUEST['books'];


?><!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <title>Mortalité, Databnf.</title>
    <script src="lib/dygraph.min.js">//</script>
    <link rel="stylesheet" type="text/css" href="lib/dygraph.css"/>
    <link rel="stylesheet" type="text/css" href="cataviz.css"/>
    <style>
    .dygraph-legend {left: 65px !important; top: 40px !important; width: 29ex !important;}
    </style>
  </head>
  <body>
    <?php include (dirname(__FILE__).'/menu.php') ?>
    <header>
      <div class="links">
        <a href="?">Auteurs français, mortalité et longévité</a> 
        | <a href="?from=1600&amp;to=2015&amp;log=1">4 siècles</a>
        | <a href="?from=1760&amp;to=1860">Révolutions</a>
        | <a href="?from=1860&amp;to=<?=$datemax?>">XX<sup>e</sup></a>
      </div>
      <form name="dates">
        <button onclick="window.location.href='?'; " type="button">Reset</button>
        De <input name="from" size="4" value="<?php echo $from ?>"/>
        à <input name="to" size="4" value="<?php echo  $to ?>"/>
        Base 100 en <input name="base100" size="4" value="<?php echo $base100 ?>"/>
        Échelle
        <button id="log" <?php if($log) echo'disabled="true"';?> type="button">log</button>
        <button id="linear" <?php if(!$log) echo'disabled="true"';?> type="button">linéaire</button>
        <button type="submit">▶</button>
      </form>
    </header>
    <div id="chart" class="dygraph"></div>
    <script type="text/javascript">
var data = [
<?php

// pas de moyenne pour ces dates
$guerres = [1789, 1790, 1791, 1792, 1793, 1794, 1870, 1871, 1914, 1915, 1916, 1917, 1918, 1939, 1940, 1941, 1942, 1943, 1944, 1945];
$guerres = array_flip($guerres);
$sql = "SELECT count(*), avg(age) FROM person WHERE fr = 1 AND gender = ? AND deathyear >= ? AND deathyear <= ? ";

if ($books > 0) $sql .= " AND books >= $books";
//
$deathq = $db->prepare($sql);

$delta100 = 3;
if ($from >= 1700) $delta100 = 1;
if ($from >= 1800) $delta100 = 0;
if ($from >= 1900) $delta100 = 0;

$deathq->execute(array(2, $base100-$delta100, $base100+$delta100));
list($countf100) = $deathq->fetch(PDO::FETCH_NUM);
$deathq->execute(array(1, $base100-$delta100, $base100+$delta100));
list($countm100) = $deathq->fetch(PDO::FETCH_NUM);

for ($date=$from; $date <= $to; $date++) {

  // femmes
  $delta = 10;
  if ($date >= 1600) $delta = 8;
  if ($date >= 1700) $delta = 6;
  if ($date >= 1789) $delta = 4;
  if ($date >= 1900) $delta = 3;
  if (isset($guerres[$date])) $delta = 0;

  $deathq->execute(array(2, $date - $delta, $date + $delta));
  list($countf, $agef) = $deathq->fetch(PDO::FETCH_NUM);
  $countf = $countf / (1 + 2 * $delta);

  $delta = 5;
  if ($date >= 1600) $delta = 4;
  if ($date >= 1700) $delta = 3;
  if ($date >= 1789) $delta = 2;
  if ($date >= 1900) $delta = 1;
  if (isset($guerres[$date])) $delta = 0;
  $deathq->execute(array(1, $date - $delta, $date + $delta));
  list($countm, $agem) = $deathq->fetch(PDO::FETCH_NUM);
  $countm = $countm / (1 + 2 * $delta);

  echo "  [".$date;
  echo ", ". number_format($agef, 2, '.', '');
  echo ", ". number_format(100.0 * $countf / $countf100, 2, '.', '');
  echo ", ". number_format($agem, 2, '.', '');
  echo ", ". number_format(100.0 * $countm / $countm100, 2, '.', '');
  echo "],\n";
}
?>];

var attrs = {
  title : "Databnf, auteurs, âge à la date de mort et indice du nombre de morts (base 100 à <?=$base100?>)",
  labels: [ "Année",
    "♀ âge à la mort", "♀ indice nb de morts",
    "♂ âge à la mort", "♂ indice nb de morts"
  ],
  ylabel: "Âge à la mort",
  y2label: "Indice nombre de morts",
  series: {
    "♀ âge à la mort": {
      color: "rgba(255, 128, 148, 1)",
      strokeWidth: 2,
    },
    "♀ indice nb de morts" : {
      color: "rgba(255, 128, 128, 0.7)",
      axis: 'y2',
      fillGraph: true,
      strokeWidth: 1,
    },
    "♂ âge à la mort": {
      color: "rgba(96, 96, 192, 1)",
      strokeWidth: 2,
    },
    "♂ indice nb de morts" : {
      color: "rgba(96, 96, 192, 0.7)",
      axis: 'y2',
      fillGraph: true,
      strokeWidth: 1,
    },
  },
};
var annoteSeries = "Morts";
<?php include('dygraph-common.php') ?>
    </script>
    <div class="text">
    <p>Pour chaque année, ce graphique projette les auteurs francophones d’au moins un livre (plus de 50 pages) à leur date de mort, avec l'âge moyen à la mort, en distinguant les sexes. Cela permet par exemple d'observer les phénomènes démographiques autour des guerres. Le nombre de morts est observé relativement à un indice, afin de rendre comparables les variations
    entre hommes et femmes. En effet, la différence entre les sexes est très importante, jusqu'à 20 fois pour le XVII<sup>e</sup> siècle. Vers 1600, il ne meurre pas une femme auteur par an, il faut attendre l'Entre-deux-Guerres pour dépasser la dizaine, il en meurre 75 aujourd'hui (contre 380 hommes). Un lissage adaptatif a été appliqué pour que la courbe reste lisible.</p>
    <p>L'empan <a href="?from=1880&to=2020&base100=1880">1880─maintenant</a> permet d'observer de nombreux phénomènes. D'abord, la moyenne d'âge à la mort augmente, suite au progrès de la médecine, après 1918, et surtout 1945. Remarquons que même durant l'Ancien-Régime, la moyenne d'âge des auteurs est supérieure à 65 ans, c'est une population bien nourrie, avec moins de risques profesionnels. Par ailleurs, la population de femmes ne cesse d'augmenter, sans compter toutes celles qui écrivent encore et ne sont pas encore comptées à leur mort. Les variations plus précises sont plus hasardeuses, sauf durant les guerres. Ainsi, 1914-1918 produit un pic de mortalité important, faisant chuter la moyenne d'âge à la mort pour les hommes tombés au champ d'honneur. Pour les femmes, on observe plutôt une surmortalité des personnes âgées, visible par une élévation de l'âge à la mort, puis à une baisse de rattrapage, le même phénomène se reproduit en 1940-1945. La seconde guerre mondiale est moins meurtrière que la première pour les hommes, le graphique permet cependant bien d'en observer le rythme, avec la défaîte en 1940, puis la montée progressive de la Résistance, de 1942 à 1944.
    </p>
    </div>
    <?php include (dirname(__FILE__).'/footer.php') ?>
  </body>
</html>
