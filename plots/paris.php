<?php
$from = 1860;
$to = 1960;
$smooth = 0;
include (dirname(__FILE__).'/Cataviz.php');
$db = new Cataviz("databnf.sqlite");


?><!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <title>Lieux d’édition, Paris, Databnf</title>
    <script src="lib/dygraph.min.js">//</script>
    <link rel="stylesheet" type="text/css" href="lib/dygraph.css"/>
    <link rel="stylesheet" type="text/css" href="cataviz.css"/>
    <style>
.dygraph-legend { left: 8% !important; }
.ann { transform: rotateZ(-90deg); transform-origin: 0% 100%; padding-left: 1em; border-left: none !important; border-bottom: 1px solid #000 !important; font-size: 16pt !important; font-weight: bold; color: rgba(0, 0, 0, 0.8) !important; }
    </style>
  </head>
  <body>
    <?php include (dirname(__FILE__).'/menu.php') ?>
    <header>
      <div class="links">
        <a href="?">Lieu d’édition</a> :
        <a href="?from=1760&amp;to=1860">Révolutions</a>,
        <a href="?from=1860&amp;to=1960">Guerres</a>,
        <a href="?from=1945&amp;to=<?=$datemax?>">Présent</a>.
      </div>
      <form name="dates">
        <button onclick="window.location.href='?'; " type="button">Reset</button>
        De <input name="from" size="4" value="<?php echo $from ?>"/>
        à <input name="to" size="4" value="<?php echo  $to ?>"/>
        smooth <input name="smooth" size="2" value="<?php echo  $smooth ?>"/>
        <button type="submit">▶</button>
      </form>
    </header>

    <div id="chart" class="dygraph"></div>

    <script type="text/javascript">
var data = [
<?php
$qtot = $db->prepare("SELECT count(*) AS count FROM document WHERE date = ? AND lang = 'fre'  AND type='Text' ");
$qparis = $db->prepare("SELECT count(*) AS count FROM document WHERE date = ? AND lang = 'fre' AND type='Text' AND paris = 1 ");
$qnul = $db->prepare("SELECT count(*) AS count FROM document WHERE date = ? AND lang = 'fre' AND type='Text' AND place IS NULL; ");
$qno_p = $db->prepare("SELECT count(*) AS count FROM document WHERE date = ? AND lang = 'fre' AND type='Text' AND pages IS NULL; ");
$qparis_p = $db->prepare("SELECT avg(pages) FROM document WHERE date = ?  AND type = 'Text' AND lang = 'fre' AND paris = 1;");
$qnotparis_p = $db->prepare("SELECT avg(pages) FROM document WHERE date = ?  AND type = 'Text' AND lang = 'fre' AND paris IS NULL;");
$adate = array();
$atot = array();
$aparis = array();
$anul = array();
$ano_p = array();
$aparis_p = array();
$anotparis_p = array();
for ($date=$from; $date <= $to; $date++) {
  $adate[] = $date;
  $qtot->execute(array($date));
  $atot[] = current($qtot->fetch(PDO::FETCH_NUM)) ;
  $qparis->execute(array($date));
  $aparis[] = current($qparis->fetch(PDO::FETCH_NUM));
  $qnul->execute(array($date));
  $anul[] = current($qnul->fetch(PDO::FETCH_NUM));
  $qno_p->execute(array($date));
  $ano_p[] = current($qno_p->fetch(PDO::FETCH_NUM));
  $qparis_p->execute(array($date));
  $aparis_p[] = current($qparis_p->fetch(PDO::FETCH_NUM));
  $qnotparis_p->execute(array($date));
  $anotparis_p[] = current($qnotparis_p->fetch(PDO::FETCH_NUM));
}
$size = count($adate);
for ($i=0; $i < $size; $i++) {
  $ifrom = $i - $smooth;
  if ($ifrom < 0) $ifrom = 0;
  $ito = $i + $smooth;
  if ($ito > $size) $ito = $size;
  $iwidth = 1+$ito-$ifrom;

  $tot = array_sum(array_slice($atot, $ifrom, $iwidth)) / $iwidth;
  $paris = array_sum(array_slice($aparis, $ifrom, $iwidth)) / $iwidth;
  $nul = array_sum(array_slice($anul, $ifrom, $iwidth)) / $iwidth;
  // $no_p = array_sum(array_slice($ano_p, $ifrom, $iwidth)) / $iwidth;
  $paris_p = array_sum(array_slice($aparis_p, $ifrom, $iwidth)) / $iwidth;
  $notparis_p = array_sum(array_slice($anotparis_p, $ifrom, $iwidth)) / $iwidth;
  echo "[".$adate[$i]
    .", ".number_format((100*$nul/$tot), 2, '.', '')
    .", ".number_format((100*$paris/$tot), 2, '.', '')
    .", ".number_format($paris_p, 2, '.', '')
    .", ".number_format($notparis_p, 2, '.', '')
  ."],\n";
}
?>];

var attrs = {
  labels: [ "Année", "+[s.l.]", "% Paris",  "Moy. pages Paris", "Moy. pages ailleurs" ],
  ylabel: "% titres en français",
  y2label: "Nombre moyen de pages",
  legend: "always",
  showRoller: true,
  stackedGraph: true,
  series: {
    "+[s.l.]": {
      color: "rgba(0, 0, 0, 0.6)",
      strokeWidth: 2,
      strokePattern: [4,4],
      fillGraph: false,
    },
    "% Paris": {
      color: "rgba(128, 128, 128, 1)",
      strokeWidth: 0,
      fillGraph: true,
    },
    "Moy. pages Paris": {
      axis: 'y2',
      color: "rgba(0, 0, 0, 1)",
      strokeWidth: 4,
      stackedGraph: false,
      fillGraph: false,
    },
    "Moy. pages ailleurs": {
      axis: 'y2',
      color: "rgba(160, 160, 255, 1)",
      strokeWidth: 4,
      stackedGraph: false,
      fillGraph: false,
    },
  },
};
var annoteSeries = "Moy. pages Paris";
<?php include('dygraph-common.php') ?>
g.ready(function() {
  var anns = g.annotations();
  g.setAnnotations(anns.concat([
    {series: "+[s.l.]", x: "1880", shortText: "1880, périodiques à l’Arsenal", width: "", height: "", cssClass: "annl"},
  ]));
});
    </script>
    <div class="text">
      <p>Les livres ont souvent un lieu d’édition, du moins, depuis la Révolution. Durant l’Ancien-Régime, les lieux indiqués sur les pages de titres ne sont pas très fiables, aussi les statisques commencent à valoir pour le XIX<sup>e</sup> et XX<sup>e</sup> s. Les titres en français sont massivement publiés à Paris. Les villes de Province publient non seulement moins, mais des documents avec moins de pages. Les capitales francophones, Bruxelles, Genève, puis Montréal, publient certes moins, mais peuvent au moins rivaliser avec Paris par le nombre de pages moyen. Dans ce contexte, les variations de la capitale, ainsi que des lieux inconnus, sont des indicateurs historiques, sur la centralisation et l’impact des événements. Ainsi, il résulte de la <a href="?from=1760&to=1860">Révolution</a> une régularisation et une concentration de l’édition à Paris. Les <a href="?from=1860&to=1960">guerres</a> affectent le nombre de pages, particulièrement à Paris, sauf pendant la Grande-Guerre, où Lille et Bruxelles sont occupées, ce qui élève la part de Paris. L’incident 1880 s’explique par la <a href="http://gallica.bnf.fr/ark:/12148/bpt6k63740997/f619.image">création</a> d’un département des périodiques à la bibliothèque de l’Arsenal, qui devient la destination de feuilles provinciales qui entraient jusqu’ici au dépôt légal normal, ce qui baisse le nombre de titres pour la Province, mais élève le nombre de pages moyen. D’autres incidents s’expliquent peuvent s’expliquer par la baisse de qualité des notices (1906–1914). La <a href="?from=1945&to=2020">période actuelle</a> se caractérise par une baisse de Paris qui tient moins à la déconcentration qu’à l’apparition de nouvelles places éditoriales, comme Arles (Actes Sud, Honoré Clair, Philippe Picquier), ou l’université de Grenoble, ainsi qu’au déménagement d’éditeurs parisiens en banlieue. Le nombre de pages moyen hors Paris reste inférieur.

      </p>
    </div>
    <?php include (dirname(__FILE__).'/footer.php') ?>
  </body>
</html>
