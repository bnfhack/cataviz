<?php
$from = 1600;
$to = 1850;
$smooth = 2;
include (dirname(__FILE__).'/Cataviz.php');
$db = new Cataviz("databnf.sqlite");

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
.ann { transform: rotateZ(-90deg); transform-origin: 0% 100%; padding-left: 1em; border-left: none !important; border-bottom: 1px solid #000 !important; font-size: 16pt !important; font-weight: bold; color: rgba(0, 0, 0, 0.4) !important; }
.ann2 { transform: rotateZ(90deg); transform-origin: 0% 0%; padding: 0 1em; border: none !important; border-top: 1px solid #000 !important; font-size: 16pt !important; font-weight: bold; color: rgba(0, 0, 0, 0.7) !important; background: rgba(255, 255, 255, 0.1); }
    </style>
  </head>
  <body>
    <?php include (dirname(__FILE__).'/menu.php') ?>
    <header>
      <div class="links">
          <a href="?">Langues anciennes</a>
        | <a href="?from=1485&amp;to=1650&amp;smooth=5">Renaissance</a>
        | <a href="?from=1600&amp;to=1840&amp;smooth=5">Lumières</a>
        | <a href="?from=1789&amp;to=1918&amp;smooth=5">XIX<sup>e</sup> s.</a>
        | <a href="?from=1900&amp;to=<?=$datemax?>">XX<sup>e</sup> s.</a>
      </div>
      <form name="dates">
        <button onclick="window.location.href='?'; " type="button">Reset</button>
        From <input name="from" size="4" value="<?php echo $from ?>"/>
        to <input name="to" size="4" value="<?php echo  $to ?>"/>
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
$sigma = 0;
if ($from < 1800) $sigma = 2;

// fre, eng, ger, ita, zxx ?, spa, lat, frm, ara, gre, chi
// part des documents avec un langue
$qgrc = $db->prepare("SELECT count(*) AS count FROM document WHERE type = 'Text' AND lang = 'grc' AND date = ? ");
$qlatant = $db->prepare("SELECT count(*) AS count FROM document WHERE type = 'Text' AND  lang = 'lat' AND birthyear < 150 AND date = ?");
$qlatmed = $db->prepare("SELECT count(*) AS count FROM document WHERE type = 'Text' AND  lang = 'lat' AND birthyear >= 150 AND birthyear < 1450 AND date = ? ");
$qlatmod = $db->prepare("SELECT count(*) AS count FROM document WHERE type = 'Text' AND  lang = 'lat' AND birthyear >= 1450 AND date = ?");
$qlatmisc = $db->prepare("SELECT count(*) AS count FROM document WHERE type = 'Text' AND  lang = 'lat' AND birthyear IS NULL AND date = ?");
$qtrad = $db->prepare("SELECT count(*) AS count FROM document WHERE  type = 'Text' AND lang IN ('frm', 'fre') AND birthyear < 1400 AND date = ?");
// il y a des images, des partitions ou des disques en latin
$qmulti = $db->prepare("SELECT count(*) AS count FROM document WHERE type IN ('Score', 'Image', 'Sound', 'MovingImage', 'StillImage') AND lang IN ('lat', 'grc') AND date = ?");

$qtext = $db->prepare("SELECT count(*) AS count FROM document WHERE  type = 'Text' AND date = ?");
$qnolang = $db->prepare("SELECT count(*) AS count FROM document WHERE  type = 'Text' AND lang IS NULL AND date = ?");

$multi = $trad = $latmod = $latant = $latmed = $latmisc = $grc = $nolang = array();
// boucler sur les dates et stocker dans un tableau pour le smooth
for ($date=$from; $date <= $to; $date++) {

  $qmulti->execute(array($date));
  $multi[$date] = current($qmulti->fetch(PDO::FETCH_NUM)) ;

  $qtrad->execute(array($date));
  $trad[$date] = current($qtrad->fetch(PDO::FETCH_NUM)) ;

  $qlatmod->execute(array($date));
  $latmod[$date] = current($qlatmod->fetch(PDO::FETCH_NUM)) ;

  $qlatant->execute(array($date));
  $latant[$date] = current($qlatant->fetch(PDO::FETCH_NUM)) ;

  $qlatmed->execute(array($date));
  $latmed[$date] = current($qlatmed->fetch(PDO::FETCH_NUM)) ;

  $qlatmisc->execute(array($date));
  $latmisc[$date] = current($qlatmisc->fetch(PDO::FETCH_NUM)) ;

  $qgrc->execute(array($date));
  $grc[$date] = current($qgrc->fetch(PDO::FETCH_NUM)) ;

  $qtext->execute(array($date));
  $qnolang->execute(array($date));
  $nolang[$date] = 100.0*current($qnolang->fetch(PDO::FETCH_NUM))/ current($qtext->fetch(PDO::FETCH_NUM));

}
// sortir le tableau de résultats
for ($date=$from; $date <= $to; $date++) {
  echo "  [".$date
    .", ".$multi[$date]
    .", ".$latmod[$date]
    .", ".$latmed[$date]
    .", ".$latmisc[$date]
    .", ".$latant[$date]
    .", ".$trad[$date]
    .", ".$grc[$date]
    .", ".$nolang[$date]
  ."],\n";

}
?>];
var attrs = {
  labels: [ "Année", "Multimédia", "Latin moderne", "Latin médiéval", "Autres latins", "Latin ancien", "Traductions", "Grec ancien", "% sans langue" ],
  ylabel: "Nombre de titres",
  showRoller: true,
  stackedGraph: true,
  fillGraph: true,
  strokeWidth: 1,
  series: {
    "Multimédia": {
      color: "rgba(0, 128, 0, 0.5)",
    },
    "Traductions": {
      color: "rgba(0, 0, 0, 0.7)",
    },
    "Latin moderne": {
      color: "rgba(255, 64, 0, 0.5)",
      // strokePattern: [5, 5],
    },
    "Latin médiéval": {
      color: "rgba(255, 0, 128, 0.5)",
      // strokePattern: [5, 5],
    },
    "Latin ancien": {
      color: "rgba(255, 0, 0, 0.7)",
    },
    "Autres latins" : {
      color: "rgba(128, 0, 0, 0.6)",
    },
    "Grec ancien": {
      color: "rgba(0, 128, 255, 0.8)",
    },
    "% sans langue": {
      axis: 'y2',
      color: "rgba(160, 160, 160, 1)",
      strokeWidth: 2,
      strokePattern: [4,4],
      stackedGraph: false,
      fillGraph: false,
    }
  },
};
var annoteSeries = "Multimédia";
<?php include('dygraph-common.php') ?>
g.ready(function() {
  var anns = g.annotations();
  g.setAnnotations(anns.concat([
    {series: "% sans langue", x: "1972", shortText: "1972", width: "", height: "", cssClass: "ann2"},
  ]));
});
    </script>
    <div class="text">
      <p>
        Les cultures anciennes se diffusent-elles également selon les siècles ?
        Un catalogue permet généralement d'obtenir la langue d'un texte
        (à l'exception de 11,1 % en moyenne, avec des variations selon les années).
        Le grec et le latin ont une grande importance symbolique pour notre culture,
        mais n'ont pas du tout le même statut. Contrairement au grec,
        le latin ne concerne pas que les auteurs antiques, il est une langue vivante
        encore publiée aujourd'hui. La BNF continue de recevoir des publications
        écclesiastiques en latin et qui ne sont pas signées par un auteur avec une date de naissance (congrégations).
        Le lien avec un auteur principal permet ainsi de distinguer plusieurs latins,
        et même, de repérer les traductions (textes en français attribué à un auteur d'avant le français).
      </p>
      <ul>
        <li><b>Multimédia</b> : images, partititions, cartes, disques films… associés au latin ou au grec ancien.</li>
        <li><b>Latin moderne</b> : textes latins d’auteurs nés après 1400, généralement religieux ou savants.</li>
        <li><b>Latin médiéval</b> : textes latins religieux, des pères de l’église à 1400.</li>
        <li><b>Autres latins</b> : textes latins sans auteur avec une date de naissance (ex : congrégations).</li>
        <li><b>Latin ancien</b> : textes latins d’un auteur né avant +150.</li>
        <li><b>Traductions</b> : textes en français d’auteurs antiques.</li>
        <li><b>Grec ancien</b> : textes en grec ancien.</li>
      </ul>

    <p>Le latin est toujours une langue très vivante, du moins, par le nombre de titres
      (significatif du nombre d'auteurs mais des lecteurs).
      Durant la <a href="?from=1485&amp;to=1650&amp;smooth=5">Renaissance</a>, du moins dans les collections
      de la bibliothèque royale, l'élan vers l'antiquité n'a pas effacé la production éditoriale ecclésiastique.
      La particularité européenne de l’Université française, dans la capitale parisienne, explique peut-être le déclin de l’édition latine durant les <a href="?from=1600&amp;to=1840&amp;smooth=5">Lumières</a>, achevées par l’Empire et sa réforme de l’enseignement supérieur. Par comparaison, le <a href="?from=1789&to=1918&smooth=5">XIX<sup>e</sup></a> semble un renouveau pour les latins religieux et savant, mais les éditions en langues anciennes sont supplantées par les traductions en français vers 1830. Au <a href="?from=1914&to=2020">XX<sup>e</sup></a> siècle, la traduction des classiques continue à progresser, les études grecques reviennent, même la latin d’église profite des progrès techniques de l’imprimerie pour diffuser plus. Si la part des langues anciennes est désormais marginale, le nombre de titres, et donc leur disponibilité, n’a jamais été aussi bonne. Enfin, il ne faut pas négliger la présence de la culture
      antique sous des formes autres qu'écrites (images, partitions, films...).
    </p>
    </div>
    <?php include (dirname(__FILE__).'/footer.php') ?>
  </body>
</html>
