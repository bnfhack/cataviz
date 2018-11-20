<?php
$from = 1780;
$to = 1958;
include (dirname(__FILE__).'/Cataviz.php');
$db = new Cataviz("databnf.sqlite");

$pagefloor = 50;
if (isset($_REQUEST['pagefloor'])) $pagefloor = $_REQUEST['pagefloor'];

?><!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <title>Chronologie, Databnf</title>
    <script src="lib/dygraph.min.js">//</script>
    <link rel="stylesheet" type="text/css" href="lib/dygraph.css"/>
    <link rel="stylesheet" type="text/css" href="cataviz.css"/>
    <style>
.dygraph-legend { left: 8% !important; }
    </style>
  </head>
  <body>
    <?php include (dirname(__FILE__).'/menu.php') ?>
    <header style="min-height: 2.7em; ">
      <div class="links">
        <a href="?">Data.bnf.fr, titres en français par an, moyenne du nombre de pages</a> :
        <a href="?from=1600&amp;to=1788&amp;smooth=4">1600–1789</a>,
        <a href="?from=1780&amp;to=1860&amp;smooth=1">1789–1870</a>,
        <a href="?from=1860&amp;to=1958&amp;smooth=1">1870–1960</a>,
        <a href="?from=1950&amp;to=<?=$datemax?>&amp;smooth=1">1950–…</a>
      </div>
      <form name="dates">
        <button onclick="window.location.href='?'; " type="button">Reset</button>
        De <input name="from" size="4" value="<?=$from?>"/>
        à <input name="to" size="4" value="<?=$to?>"/>
        <label>Seuil pages <input name="pagefloor" size="4" value="<?php echo  $pagefloor ?>"/></label>
        <label>Lissage <input name="smooth" size="1" value="<?php echo  $smooth ?>"/></label>
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

if ($from < 1610) {
  $qbook = $db->prepare("SELECT count(*) AS count FROM document WHERE date = ? AND type = 'Text' AND pages >= ? AND (lang='fre' OR lang='frm') ");
  $qbroch = $db->prepare("SELECT count(*) AS count FROM document WHERE date = ? AND type = 'Text' AND pages < ? AND (lang='fre' OR lang='frm') ");
  $qtot = $db->prepare("SELECT count(*) AS count FROM document WHERE date = ? AND type = 'Text' AND (lang='fre' OR lang='frm') ");
  $qpages = $db->prepare("SELECT avg(pages) FROM document WHERE date = ?  AND type = 'Text' AND (lang='fre'  OR lang='frm')");
}
else {
  $qbook = $db->prepare("SELECT count(*) AS count FROM document WHERE date = ? AND type = 'Text' AND pages >= ? AND lang='fre' ");
  $qbroch = $db->prepare("SELECT count(*) AS count FROM document WHERE date = ? AND type = 'Text' AND pages < ? AND lang='fre' ");
  $qtot = $db->prepare("SELECT count(*) AS count FROM document WHERE date = ? AND type = 'Text'  AND lang='fre' ");
  $qpages = $db->prepare("SELECT avg(pages) FROM document WHERE date = ?  AND type = 'Text' AND lang='fre'");
}

/*
if ($from < 1610)
  $qpages2 = $db->prepare("SELECT sum(pages) FROM document WHERE date = ?  AND type = 'Text' ");
else
  $qpages2 = $db->prepare("SELECT sum(pages) FROM document WHERE date = ?  AND type = 'Text' ");
*/

$lastpages = 0;
for ($date=$from; $date <= $to; $date++) {
  $qbook->execute(array($date, $pagefloor));
  list($book) = $qbook->fetch(PDO::FETCH_NUM);
  $qbroch->execute(array($date, $pagefloor));
  list($broch) = $qbroch->fetch(PDO::FETCH_NUM);
  $qtot->execute(array($date));
  list($tot) = $qtot->fetch(PDO::FETCH_NUM);

  $qpages->execute(array($date));
  list($pages) = $qpages->fetch(PDO::FETCH_NUM);
  if (!$pages) {
    $pages = $lastpages;
  }
  else {
    $lastpages = $pages;
  }
  echo "[".$date;
  echo ", ".($tot - $broch - $book);
    // .", ".number_format((100*$nop/$tot), 2, '.', '')
  // echo  ", ".$text;
  echo ", ".$broch;
  echo ", ".$book;
  echo ", ".number_format($pages, 2, '.', '');
  // echo  ", ".$pages2;
  echo "],\n";
}
// nom de colonnes
$A = "Titres > $pagefloor p.";
$B = "Titres <= $pagefloor p.";
?>]; // data

attrs = {
  title : "Databnf, nombre de titres à la date de publication.",
  labels: [ "Année", "Titres ? p.", "<?=$B?>", "<?=$A?>", "Moy. pages"], // "Moy. pages",
  legend: "always",
  ylabel: "Titres",
  y2label: "Pages",
  stackedGraph: true,
  showRoller: true,
  series: {
    "Titres ? p.": {
      color: "rgba(1, 1, 1, 1)",
      strokeWidth: 1,
      fillGraph: false,
    },
    "<?=$B?>": {
      color: "rgba(128, 128, 128, 1)",
      strokeWidth: 0,
    },
    "<?=$A?>": {
      color: "rgba(64, 64, 64, 0.5)",
      strokeWidth: 1,
    },
    "Moy. pages": {
      axis: 'y2',
      color: "rgba(255, 128, 128, 1)",
      strokeWidth: 2,
      strokePattern: [4,4],
      stackedGraph: false,
      fillGraph: false,
    },
  },
};
var annoteSeries = "Titres ? p.";
<?php include('dygraph-common.php') ?>
    </script>
    <div class="text">
    <p>
Le nombre de titres français par an qui entrent au catalogue augmente beaucoup au cours des siècles,
de quelques titres au début de la bibliothèque (1537), à plusieurs dizaines de milliers de nos jours.
Les effectifs sont à observer en tendance, car ils sont affectés par différents taux d’erreur qui se combinent.
7% des notices sont sans date, 11% n’ont pas de langue, le taux de documents sans nombre de pages (repérable automatiquement)
est variable.
La variation du nombre moyen de pages reste cependant très significative des événements historiques (guerres, révolutions…),
affectant une édition très concentrée à <a href="paris.php">Paris</a>.
On remarque par exemple que l’agitation de la Fronde, de 1789 ou de 1848 produit beaucoup de titres de peu de pages (brochures)
mais affecte beaucoup moins les nombre de titres de plus de 100 pages.
Les guerres, par contre, affectent durement tous les genres éditoriaux (restrictions de papier, blocage de Paris).
  </p>
</div>
  <?php include (dirname(__FILE__).'/footer.php') ?>
  </body>
</html>
