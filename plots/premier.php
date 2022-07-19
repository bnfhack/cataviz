<?php
$from = 1760;
$to = 1960;
$datemax = 1960;
include (dirname(__FILE__).'/Cataviz.php');
$db = new Cataviz("databnf.sqlite");
// pour indice 100, même delta pour toutes les lignes ?
$base100 = floor (($from + ($to - $from)/2.0) / 10.0) * 10;
if (isset($_REQUEST['base100']) && $_REQUEST['base100'] >= $from && $_REQUEST['base100'] <= $to) $base100 = $_REQUEST['base100'];

?><!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <title>Âge à la publication, Databnf</title>
    <script src="lib/dygraph.min.js">//</script>
    <link rel="stylesheet" type="text/css" href="lib/dygraph.css"/>
    <link rel="stylesheet" type="text/css" href="cataviz.css"/>
    <style>
    .dygraph-legend {left: 8% !important; top: 40px !important; width: 18em;}
    </style>
  </head>
  <body>
    <?php include (dirname(__FILE__).'/menu.php') ?>
    <header>
      <div class="links">
        <a href="?">Âge à la publication</a> :
        <a href="?from=1750&amp;to=1865">Révolutions</a>,
        <a href="?from=1910&amp;to=<?=$datemax?>">XX<sup>e</sup></a>.
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
data = [
<?php

$agefq  = $db->prepare("SELECT avg(age) FROM document WHERE lang = 'fre' AND book = 1 AND gender=2 AND date >= ? AND date <= ?");
$firstfq = $db->prepare("SELECT avg(age1) FROM person WHERE fr = 1 AND gender=2 AND opus1 >= ? AND opus1 <= ? ");
$countfq = $db->prepare("SELECT count(*) AS count FROM document WHERE lang='fre' AND book=1 AND posthum = 0 AND gender = 2 AND document.date >= ? AND document.date <= ? ");

$agemq  = $db->prepare("SELECT avg(age) FROM document WHERE lang = 'fre' AND book = 1 AND gender=1 AND date >= ? AND date <= ?");
$firstmq = $db->prepare("SELECT avg(age1) FROM person WHERE fr = 1 AND gender=1 AND opus1 >= ? AND opus1 <= ? ");
$countmq = $db->prepare("SELECT count(*) AS count FROM document WHERE lang='fre' AND book=1 AND posthum = 0 AND gender = 1 AND document.date >= ? AND document.date <= ? ");


$delta = Cataviz::delta(2, $base100);
$d = floor($delta * 0.5);
$countfq->execute(array($base100 - $d, $base100 + $d));
list($countf100) = $countfq->fetch(PDO::FETCH_NUM);
$countf100 = $countf100 / (1 + 2 * $d);

$delta = Cataviz::delta(1, $base100);
$d = floor($delta * 0.5);
$countmq->execute(array($base100 - $d, $base100 + $d));
list($countm100) = $countmq->fetch(PDO::FETCH_NUM);
$countm100 = $countm100 / (1 + 2 * $d);



for ($date=$from; $date <= $to; $date++) {

  /*
  $ageq->execute(array($date));
  list($age) = $ageq->fetch(PDO::FETCH_NUM);
  */

  $delta = Cataviz::delta(2, $date);

  $d = floor($delta);
  $agefq->execute(array($date - $d, $date + $d));
  list($agef) = $agefq->fetch(PDO::FETCH_NUM);

  $d = floor($delta * 2);
  $firstfq->execute(array($date - $d, $date + $d));
  list($f) = $firstfq->fetch(PDO::FETCH_NUM);
  if ($f) $firstf = $f;

  $d = floor($delta * 0.3);
  $countfq->execute(array($date - $d, $date + $d));
  list($countf) = $countfq->fetch(PDO::FETCH_NUM);
  $countf = $countf / (1 + 2 * $d);

  $delta = Cataviz::delta(1, $date);

  $d = floor($delta * 0.6);
  $agemq->execute(array($date - $d, $date + $d));
  list($agem) = $agemq->fetch(PDO::FETCH_NUM);

  $d = floor($delta * 0.8);
  $firstmq->execute(array($date - $d, $date + $d));
  list($firstm) = $firstmq->fetch(PDO::FETCH_NUM);

  $d = floor($delta * 0.3);
  $countmq->execute(array($date - $d, $date + $d));
  list($countm) = $countmq->fetch(PDO::FETCH_NUM);
  $countm = $countm / (1 + 2 * $d);


  echo "  [".$date;

  if (!$agef) echo ', ';
  else echo ", ".number_format($agef, 2, '.', '');
  echo ", ".number_format($firstf, 2, '.', '');
  echo ", ". number_format(100.0* $countf  / $countf100, 2, '.', '');

  if (!$agem) echo ',';
  else echo ", ".number_format($agem, 2, '.', '');
  echo ", ".number_format($firstm, 2, '.', '');
  echo ", ". number_format(100.0* $countm  / $countm100, 2, '.', '');

  echo "],\n";

}
?>];
attrs = {
  title : "Databnf, âges moyens à la date de publication (livres, base 100 en <?=$base100?>).",
  labels: [ "Année",
    "♀ Âge à la publication", "♀ Âge au 1er livre", "♀ Livres",
    "♂ Âge à la publication", "♂ Âge au 1er livre", "♂ Livres",
  ],
  ylabel: "Âge moyen",
  y2label: "Livres, base 100 en <?=$base100?>",
  series: {
    "♀ Âge à la publication": {
      axis: 'y',
      color: "rgba(255, 128, 128, 0.7)",
      strokeWidth: 6,
    },
    "♀ Âge au 1er livre": {
      axis: 'y',
      color: "rgba(255, 128, 128, 0.7)",
      strokeWidth: 6,
      strokePattern: [6, 6],
    },
    "♀ Livres": {
      axis: 'y2',
      color: "rgba(255, 192, 192, 1)",
      strokeWidth: 1,
      fillGraph: true,
    },
    "♂ Âge à la publication": {
      axis: 'y',
      color: "rgba(128, 128, 192, 0.7)",
      strokeWidth: 6,
    },
    "♂ Âge au 1er livre": {
      axis: 'y',
      color: "rgba(128, 128, 192, 0.7)",
      strokeWidth: 6,
      strokePattern: [6, 6],
    },
    "♂ Livres": {
      axis: 'y2',
      color: "rgba(192, 192, 255, 1)",
      strokeWidth: 1,
      fillGraph: true,
    },
  },
};
var annoteSeries = "♂ Livres";
<?php include('dygraph-common.php') ?>
g.ready(function() {
  var anns = g.annotations();
  g.setAnnotations(anns.concat([
    {series: "♂ Âge à la publication", x: "1802", shortText: "Lycées napoléoniens", width: "", height: "", cssClass: "ann2"},
  ]));
});

    </script>
    <div class="text">
    <p>
      Ce graphique agrège des informations pour comprendre l’âge moyen à la publication d’un livre selon le sexe de l'auteur principal.
      En démographie, cette donnée pourrait correspondre à l'âge moyen à la naissance d'un enfant.
      L'âge est une variable d'ajustement du marché lorsque les débouchés à la vente augmentent ou diminuent.
      Attention, les chiffres sont de moins en moins fiables au fur et à mesure que l'on s'approche du présent,
      car les dates de naissance ne sont pas encore renseignées pour les auteurs récents
      (voir la projection des auteurs à leur date de naissance, la <a href="natalite.php?from=1900&to=2016&books=10">«natalité»</a>).
    </p>
    <p>
      Ainsi, autour de la <a href="?from=1750&to=1840">Révolution</a>, on peut observer plusieurs phénomènes.
      Avant 1789, l'âge moyen au premier livre est relativement stable (entre 38 et 40 ans pour les hommes, largement majoritaires).
      On observe cependant une accélération du rythme de publications nouvelles, qui se traduit par un âge moyen en diminution.
      L'augmentation du nombre de titres s'explique par des progrès techniques, une relative prospérité économique,
      des progrès dans l'alphabétisation des lecteurs, mais pas de nouvelles structures éducatives qui formeraient plus d'auteurs.
      Il en résulte que les mêmes écrivent plus, peuvent en dégager un revenu, ce dont témoigne par exemple Beaumarchais
      voulant protéger les droits des auteurs contre les imprimeurs (1777). La Révolution, jusqu'à la fin de la Terreur (1795),
      montre une chute du nombre de livres (largement compensée par une <a href="chrono.php?from=1750&to=1865" target="_blank">agitation éditoriale</a>
      importante sur des formats plus courts), parallèle à la mort et l'exil d'auteurs, ce qui ouvre des places aux jeunes.
      De 1795 à 1815, le nombre de titre est bas, l'économie est en effort de guerre, et la censure s'active.
      Les auteurs révolutionnaires encore vivants, ainsi que les jeunes formés par les lycées napoléoniens,
      se bousculent mais ne peuvent pas rentrer sur un marché de plus en plus concurrentiel, tenu par les plus anciens,
      l'âge à la publication augmente.
      La fin de Napoléon libère la prospérité économique, l'industrialisation, ouvrant le marché des titres à la génération romantique.
    </p>
    </div>
    <?php include (dirname(__FILE__).'/footer.php') ?>
  </body>
</html>
