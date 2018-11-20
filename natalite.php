<?php
$datemax = 1990;
$from = 1830;
$to = 1950;
include (dirname(__FILE__).'/Cataviz.php');
$db = new Cataviz("databnf.sqlite");
if (!isset($_REQUEST['books'])) $books = 10;
else $books = $_REQUEST['books'];
if (!$books || $books < 0) $books = 0;


?><!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <title>Natalité des auteurs, Databnf.</title>
    <script src="lib/dygraph.min.js">//</script>
    <link rel="stylesheet" type="text/css" href="lib/dygraph.css"/>
    <link rel="stylesheet" type="text/css" href="cataviz.css"/>
    <style>
    .dygraph-legend { left: 65px !important; top: 40px !important; width: 22ex !important; }
    </style>
  </head>
  <body>
    <?php include (dirname(__FILE__).'/menu.php') ?>
    <header>
      <div class="links">
        <a href="?">Natalité des auteurs francophones</a>
        | <a href="?from=1500&amp;to=1990&amp;log=true">5 siècles</a>
        | <a href="?from=1690&amp;to=1820&amp;log=">Révolution</a> 
        | <a href="?from=1840&amp;to=1950&amp;log=true">guerres mondiales</a> 
      </div>
      <form name="dates">
        <button onclick="window.location.href='?'; " type="button">Reset</button>
        De <input name="from" size="4" value="<?php echo $from ?>"/>
        à <input name="to" size="4" value="<?php echo  $to ?>"/>
        <label title="Nombre de livre minimum que doit avoir signé l’auteur">Seuil livres <input name="books" size="4" value="<?php echo $books ?>"/></label>

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
$qm = $db->prepare("SELECT count(*) AS count FROM person WHERE fr = 1 AND gender = 1  AND birthyear >= ? AND birthyear <= ?");
$qf = $db->prepare("SELECT count(*) AS count FROM person WHERE fr = 1 AND gender = 2 AND birthyear >= ? AND birthyear <= ?");
if ($books) {
  $qmbooks = $db->prepare("SELECT count(*) AS count FROM person WHERE fr = 1 AND gender = 1 AND birthyear >= ? AND birthyear <= ? AND books >= ?");
  $qfbooks = $db->prepare("SELECT count(*) AS count FROM person WHERE fr = 1 AND gender = 2 AND birthyear >= ? AND birthyear <= ? AND books >= ?");
}

$msigma = 1;


for ($date=$from; $date <= $to; $date++) {
  $guerres = [ 1695, 1696, 1697, 1698, 1699, 1700, 1701, 1702, 1703, 1704, 1705, 1706, 1707, 1708, 1709, 1710, 1870, 1871, 1914, 1915, 1916, 1917, 1918, 1939, 1940, 1941, 1942, 1943, 1944, 1945 ];
  $guerres = array_flip($guerres);

  $fsigma = 5;
  if ($date>= 1740) $fsigma = 3;
  if ($date>= 1814) $fsigma = 2;
  if ($date>= 1865) $fsigma = 0;
  if ($date > 1800 && isset($guerres[$date])) $fsigma = 0;
  // if ($date>= 1925) $fsigma = 0;
  $qf->execute(array($date-$fsigma, $date+$fsigma));
  list($fcount) = $qf->fetch(PDO::FETCH_NUM);
  $fcount = $fcount / (1+2*$fsigma);

  if ($books) {
    if ($books < 5);
    else if ($date < 1800) $fsigma = 5;
    else $fsigma=$fsigma+2;
    $qfbooks->execute(array($date-$fsigma, $date+$fsigma, $books));
    list($fbooks) = $qfbooks->fetch(PDO::FETCH_NUM);
    $fbooks = $fbooks / (1+2*$fsigma);
  }

  $msigma = 6;
  if ($date>= 1700) $msigma = 2;
  if ($date>= 1755) $msigma = 2;
  if ($date>= 1796) $msigma = 1;
  if ($date>= 1850) $msigma = 0;
  if (isset($guerres[$date])) $fsigma = 0;
  $qm->execute(array($date-$msigma, $date+$msigma));
  list($mcount) = $qm->fetch(PDO::FETCH_NUM);
  $mcount = $mcount / (1+2*$msigma);
  if ($books) {
    $qmbooks->execute(array($date-$msigma, $date+$msigma, $books));
    list($mbooks) = $qmbooks->fetch(PDO::FETCH_NUM);
    $mbooks = $mbooks / (1+2*$msigma);
  }

  echo "  [".$date;
  echo ",".number_format($fcount, 2, '.', '');
  if ($books) echo ",".number_format($fbooks, 2, '.', '');
  echo ",".number_format($mcount, 2, '.', '');
  if ($books) echo ",".number_format($mbooks, 2, '.', '');
  // echo ",". number_format(100.0 * $fcount / ($fcount + $mcount), 2, '.', '');
  // if ($books) echo ",". number_format(100.0 * $fbooks / ($fbooks + $mbooks), 2, '.', '');
  echo "],\n";
}  ?>
];
// "% femmes",<?php if ($books) echo " \"% ♀ > $books livres\",";?>

var attrs = {
  title : "Databnf, par année, nombre d’auteurs qui naissent.",
  labels: [
    "Année",
    "Femmes", <?php if ($books) echo " \"♀ > $books livres\",";?>
    "Hommes",<?php if ($books) echo " \"♂ > $books livres\",";?>
  ],
  ylabel: "Naissances d’auteurs",
  y2label: "Part des femmes",
  series: {
    "Femmes": {
      axis: 'y',
      color: "rgba(255, 128, 128, 1)",
      strokeWidth: 1,
      fillGraph: true,
    },
    "♀ > <?=$books?> livres": {
      axis: 'y',
      color: "rgba(255, 0, 0, 1)",
      strokeWidth: 2,
      strokePattern: [4,4],
    },
    "Hommes": {
      axis: 'y',
      color: "rgba(192, 192, 255, 1)",
      strokeWidth: 1,
      fillGraph: true,
    },
    "♂ > <?=$books?> livres": {
      axis: 'y',
      color: "rgba(0, 0, 128, 1)",
      strokeWidth: 2,
      strokePattern: [4,4],
    },
    "% femmes": {
      axis: 'y2',
      color: "rgba(192, 192, 192, 0.5)",
      strokeWidth: 4,
    },
    "% ♀ > <?=$books?> livres": {
      axis: 'y2',
      color: "rgba(192, 192, 192, 0.7)",
      strokeWidth: 4,
      strokePattern: [4,4],
    },
  },
};
<?php include('dygraph-common.php') ?>
g.ready(function() {
  g.setAnnotations([
    <?php
    if ($to-$from < 210) {
      echo '
    { series: "Hommes", x: "1606", shortText: "1606, Corneille", width: "", height: "", cssClass: "ann45", },
    { series: "Hommes", x: "1622", shortText: "1622, Molière", width: "", height: "", cssClass: "ann45", },
    { series: "Hommes", x: "1639", shortText: "1639, Racine", width: "", height: "", cssClass: "ann45", },
    { series: "Hommes", x: "1694", shortText: "1694, Voltaire", width: "", height: "", cssClass: "ann-45", },
    { series: "Hommes", x: "1712", shortText: "1712, Rousseau", width: "", height: "", cssClass: "ann-45", },
    { series: "Hommes", x: "1743", shortText: "1743, Condorcet", width: "", height: "", cssClass: "ann-45", },
    { series: "Hommes", x: "1783", shortText: "1783, Stendhal", width: "", height: "", cssClass: "ann45", },
    { series: "Hommes", x: "1799", shortText: "1799, Balzac", width: "", height: "", cssClass: "ann45", },
    // { series: "Hommes", x: "1821", shortText: "1821, Baudelaire", width: "", height: "", cssClass: "ann45", },
    { series: "Hommes", x: "1854", shortText: "1854, Rimbaud", width: "", height: "", cssClass: "ann45", },
    { series: "Hommes", x: "1887", shortText: "Mobilisables", width: "", height: "", cssClass: "ann-45", },
    { series: "Hommes", x: "1905", shortText: "1905, Sartre", width: "", height: "", cssClass: "ann-45", },

    { series: "Femmes", x: "1634", shortText: "1634, La Fayette", width: "", height: "", cssClass: "ann-45", },
    { series: "Femmes", x: "1748", shortText: "1748, de Gouges", width: "", height: "", cssClass: "ann-45", },
    { series: "Femmes", x: "1766", shortText: "1766, de Staël", width: "", height: "", cssClass: "ann-45", },
    // { series: "Femmes", x: "1799", shortText: "1799, de Ségur", width: "", height: "", cssClass: "ann-45", },
    { series: "Femmes", x: "1804", shortText: "1804, Sand", width: "", height: "", cssClass: "ann-45", },
    { series: "Femmes", x: "1908", shortText: "1908, Beauvoir", width: "", height: "", cssClass: "ann-45", },

    { series: "% femmes", x: "1789", shortText: "1789", width: "", height: "", cssClass: "annl", },
    { series: "Femmes", x: "1850", shortText: "1850, écoles de filles", width: "", height: "", cssClass: "annl", },
    { series: "% femmes", x: "1870", shortText: "1870", width: "", height: "", cssClass: "annl", },
    { series: "Femmes", x: "1879", shortText: "1879, lois enseignement", width: "", height: "", cssClass: "annl", },
    // { series: "% femmes", x: "1914", shortText: "1914", width: "", height: "", cssClass: "annl", },
    // { series: "% femmes", x: "1939", shortText: "1939", width: "", height: "", cssClass: "annl", },
      ';
    }
    ?>
  ]);
});
    </script>
    <div class="text">
    <p>Ce graphique projette l’effectif des auteurs francophones à leur date de naissance, avec le ratio sexuel. Les effectifs sont parfois très faibles selon les périodes (1600 : 1 femme et 61 hommes), agitant les courbes de bruits accidentels. Une moyenne glissante variable a été appliquée, de +/- 10 ans pour les femmes avant 1750, à rien pour le XX<sup>e</sup> siècle. Cela permet d’obtenir des courbes lisibles sur <a href="?from=1500&to=1990&log=true">5 siècles</a>. La baisse des “naissances” après 1950 s’explique par un <a target="_blank" href="premier.php?from=1900&to=2015">âge moyen au premier livre</a> qui ne cesse d’augmenter, du moins, selon les données <i>Databnf</i>. Il n’y a probablement pas moins d’auteurs qui naissent après 1950, mais ils ne sont pas encore entrés au catalogue. Remarquons aussi que les femmes publient leur premier livre plus jeunes que les hommes. La parité pour les auteurs nés dans les années 1990 (la vingtaine) pourrait ne pas durer. Sur les conseils de <a href="https://twitter.com/olivier_ritz">Olivier Ritz</a>, un champ permet de filtrer les auteurs ayant signé des livres (> 50 p.), ce qui s’avère très utile pour la période révolutionnaire, où le catalogue enregistre une grande activité politique d’écrits courts qui ne sont pas exactement des livres.</p>
    <p>Les <a href="?from=1600&to=1750&log=true">Lumières</a> s’illustrent non seulement par le nombre de titres, mais aussi par un nombre d’auteurs en croissance logarithmique. Le nombre de femmes croît aussi, mais sans modifier une proporion très basse, de 2 % à 4 %. On notera l’effet des perturbations climatiques autour de 1700, qui produit un déficit de naissances, global dans la population, confirmé par une <a href="?from=1650&amp;to=1750" target="_blank">baisse de l’âge à la mort</a>.</p>
    <p>La <a href="?from=1690&to=1820&log=true">Révolution</a> est une période troublée, qui peut expliquer une désaffection relative envers les lettres pour la génération de 1754 (35 ans en 1789), quoique plus pour les auteurs d’écrits courts, souvent politiques, que les auteurs de livres. L’effet de rattrapage est ensuite très rapide pour la génération romantique. Sur un effectif assez stable de femmes auteur, les variations masculines suffisent à modifier des proportions relatives qui de toute façon restent faibles (5,5 % au maximum en 1783).</p>

    <p>Le traumatisme démographique de la <a href="?from=1850&to=1950&log=true">Grande-Guerre</a> crante la courbe des hommes et des femmes à naître, ce sont les auteurs qui n’ont pas été conçus pendant la mobilisation, jusque 1918 plus 9 mois (1919). Un cran similaire s’observe pour 1870, mais pas pour la guerre 1940 (exil ?). La guerre 1914 a aussi un effet sur la génération mobilisable des hommes,  ce qui explique ce faux plat dans les naissances entre 1867 et 1909, dans un contexte de croissance. Sartre explique comment il a l’impression d’arriver dans un monde sans hommes, le laissant libre d’aller à sa carrière. Pour les femmes, la cause la plus importante de la croissance s’explique certainement par les différentes lois sur l’eneignement de la 3<sup>e</sup> République : 1879, École normale de jeunes filles dans tous les départements ; 1880, ouverture de la Sorbonne aux femmes, lycée de jeunes filles ; 1881, école obligatoire pour les garçon <i>et</i> les filles.</p>
    </div>
    <?php include (dirname(__FILE__).'/footer.php') ?>
  </body>
</html>
