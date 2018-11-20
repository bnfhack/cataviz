<?php
$smooth = 0;
$from = 1770;
$to = 1880;
$smooth = 0;
$log = NULL;
include (dirname(__FILE__).'/Cataviz.php');
$db = new Cataviz("databnf.sqlite");
if (!isset($_REQUEST['books'])) $books = 10;
else $books = $_REQUEST['books'];
if (!$books || $books < 0) $books = 0;


?><!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <title>Population des auteurs, Databnf.</title>
    <script src="lib/dygraph.min.js">//</script>
    <link rel="stylesheet" type="text/css" href="lib/dygraph.css"/>
    <link rel="stylesheet" type="text/css" href="cataviz.css"/>
    <style>
    .dygraph-legend { left: 65px !important; top: 40px !important; width: 25ex !important; }
    </style>
  </head>
  <body>
    <?php include (dirname(__FILE__).'/menu.php') ?>
    <header>
      <div class="links">
        <a href="" target="_new">Population des auteurs </a> 
        | <a href="?from=1760&amp;to=1860">Révolution</a>
        | <a href="?from=1860&amp;to=<?=$datemax?>">1860–…</a>.
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
data = [
<?php
$doc1q = $db->prepare("SELECT gender, count(*) AS count FROM person WHERE fr = 1 AND doc1 <= ? AND (deathyear >= ? OR deathyear IS NULL) GROUP BY gender ORDER BY gender ");
$booksq = $db->prepare("SELECT gender, count(*) AS count FROM person WHERE fr = 1 AND doc1 <= ? AND (deathyear >= ? OR deathyear IS NULL) AND books >= ? GROUP BY gender ORDER BY gender ");

$fcount = 0;
$mcount = 0;
for ($date=$from; $date <= $to; $date++) {
  $doc1q->execute(array($date, $date));
  while ($row = $doc1q->fetch(PDO::FETCH_NUM)) {
    if ($row[0] === null) continue;
    if ($row[0] == 1) $mdoc1 = $row[1];
    if ($row[0] == 2) $fdoc1 = $row[1];
  }
  if ($books) {
    $booksq->execute(array($date, $date, $books));
    while ($row = $booksq->fetch(PDO::FETCH_NUM)) {
      if ($row[0] === null) continue;
      if ($row[0] == 1) $mbooks = $row[1];
      if ($row[0] == 2) $fbooks = $row[1];
    }
  }
  echo "[".$date;
  echo ",".($fdoc1);
  if ($books) {
    echo ",".($fbooks);
    // echo ",". number_format((100.0 * $fbooks / $fdoc1), 2, '.', '');
  }
  echo ",".($mdoc1);
  if ($books) {
    echo ",".($mbooks);
    // echo ",". number_format((100.0 * $mbooks / $mdoc1), 2, '.', '');
  }
  echo ",". number_format((100.0 * $fdoc1 / ($fdoc1+$mdoc1)), 2, '.', '');
  if ($books) {
    echo ",". number_format((100.0 * $fbooks / ($fbooks+$mbooks)), 2, '.', '');
  }
  echo "],\n";
}

/*
"Moy. pages": {
  axis: 'y2',
  color: "rgba(128, 128, 128, 0.5)",
  strokeWidth: 5,
},

*/
?>];

var attrs = {
  title : "Databnf, par année, population d’auteurs vivants.",
  labels: [ "Année"
    , "Femmes", <?php if ($books) echo " \"♀ > $books livres\""; // , \"% ♀ > $books livres\", "?>
    , "Hommes", <?php if ($books) echo " \"♂ > $books livres\""; // , \"% ♂ > $books livres\", "?>
    , "♀ % ♂", <?php if ($books) echo " \"♀ % ♂ > $books livres\"" ?>
  ],
  legend: "always",
  ylabel: "Auteurs vivants",
  y2label: "%",
  showRoller: true,
  series: {
    "Femmes": {
      color: "rgba(255, 128, 128, 1)",
      strokeWidth: 1,
      fillGraph: true,
    },
    "♀ > <?=$books?> livres": {
      color: "rgba(255, 128, 128, 0.5)",
      strokeWidth: 4,
      strokePattern: [4,4],
    },
    "% ♀ > <?=$books?> livres": {
      axis: 'y2',
      color: "rgba(255, 128, 192, 0.7)",
      strokeWidth: 4,
      strokePattern: [4,4],
    },
    "Hommes": {
      color: "rgba(96, 96, 192, 0.5)",
      strokeWidth: 1,
      fillGraph: true,
    },
    "♂ > <?=$books?> livres": {
      color: "rgba(0, 0, 128, 1)",
      strokeWidth: 4,
      strokePattern: [4,4],
    },
    "% ♂ > <?=$books?> livres": {
      axis: 'y2',
      color: "rgba(96, 128, 192, 1)",
      strokeWidth: 1,
      strokePattern: [4,4],
    },
    "♀ % ♂": {
      axis: 'y2',
      color: "rgba(192, 192, 192, 1)",
      strokeWidth: 4,
      fillGraph: false,
    },
    "♀ % ♂ > <?=$books?> livres": {
      axis: 'y2',
      color: "rgba(128, 128, 128, 0.7)",
      strokeWidth: 4,
      strokePattern: [4,4],
    },
  },
};
var annoteSeries = "Hommes";
<?php include('dygraph-common.php') ?>

    </script>
    <div class="text">
    <p>Ce graphique cherche à établir une population d’auteurs vivants pour chaque année. Un auteur sera dit entré dans la vie culturelle à la date du premier document qu’il signe, et en sort à sa mort.
     Un champ du formulaire permet de distinguer les auteurs qui ont signé plusieurs “livres” (document écrit de plus de 50 pages).
     Ce calcul réagit vite à l’entrée de nouveaux auteurs dans la carrière, par contre, il y a une inertie à la sortie. Cette population ne participe peut-être moins à la vie intellectuelle, mais elles forment tout de même une masse de gens instruits qui doit peser dans le public.</p>
    <p>La part des femmes est très faible, moins de 5 % pendant des siècles. Cette proportion croît de façon continue depuis 1860, mais avant, il y a eu des points d’inflexion, notamment autour de la Révolution.</p>
    </div>
    <?php include (dirname(__FILE__).'/footer.php') ?>
  </body>
</html>
