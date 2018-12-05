<?php
// header('Content-type: text/plain; charset=utf-8');
$from = 1865;
$tomax = 2015;
$smooth = 0;

include (dirname(__FILE__).'/Cataviz.php');
$db = new Cataviz("databnf.sqlite");

$max = @$_REQUEST['max'];

?><!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <title>Siècles, rééditions, Databnf</title>
    <script src="lib/dygraph.min.js">//</script>
    <link rel="stylesheet" type="text/css" href="lib/dygraph.css"/>
    <link rel="stylesheet" type="text/css" href="cataviz.css"/>
    <style>
.dygraph-legend { left: 60px !important; }
#header { min-height: 2.7em;  }
    </style>
  </head>
  <body>
    <?php include (dirname(__FILE__).'/menu.php') ?>
    <header id="header">
      <div class="links">
        <a href="?">Titres d’auteurs morts à la date de publication, colorés selon le siècle de naissance</a> |
        <a href="?from=1450&amp;to=1640&amp;smooth=4">1450–1640</a> |
        <a href="?from=1640&amp;to=1780&amp;smooth=4">1640-1780</a> |
        <a href="?from=1780&amp;to=1865">1780-1865</a> |
        <a href="?from=1865&amp;to=1962">1865-1962</a>
      </div>
      <form name="dates">
        <button onclick="window.location.href='?'; " type="button">Reset</button>
        De <input name="from" size="4" value="<?php echo $from ?>"/>
        à <input name="to" size="4" value="<?php echo  $to ?>"/>
        Zoom
        <button type="button" onclick="var options = {}; var max = g.yAxisRange()[1] /1.5; options.valueRange = [ 0, max]; g.updateOptions(options); ">▼</button>
        <button type="button" onclick="var options = {}; var max = g.yAxisRange()[1] *1.5; options.valueRange = [ 0, max]; g.updateOptions(options); ">▲</button>
        <button type="submit">▶</button>
      </form>
    </header>
    <div id="chart" class="dygraph"></div>
    <script type="text/javascript">
var data = [
<?php
// fre, eng, ger, ita, zxx ?, spa, lat, frm, ara, gre, chi
// hack pour un index couvrant, pour les vivants, additionner
$qtot = $db->prepare("SELECT count(*) AS count FROM document WHERE date = ? AND book = 1 AND lang = 'fre'; ");
$qpost = $db->prepare("SELECT count(*) AS count FROM document WHERE date = ? AND book = 1 AND lang = 'fre' AND posthum=1; ");


$qant = $db->prepare("SELECT count(*) AS count FROM document WHERE date = ? AND book = 1 AND posthum=1 AND birthyear < 150; ");
$q500 = $db->prepare("SELECT count(*) AS count FROM document WHERE date = ? AND book = 1 AND posthum=1 AND birthyear >= 150 AND birthyear < 1450; ");
$q1450 = $db->prepare("SELECT count(*) AS count FROM document WHERE date = ? AND book = 1 AND posthum=1 AND birthyear >= 1450 AND birthyear < 1600; ");
$q1600 = $db->prepare("SELECT count(*) AS count FROM document WHERE date = ? AND book = 1 AND lang = 'fre' AND posthum=1 AND birthyear >= 1600 AND birthyear < 1680; ");
$q1690 = $db->prepare("SELECT count(*) AS count FROM document WHERE date = ? AND book = 1 AND lang = 'fre' AND posthum=1 AND birthyear >= 1680 AND birthyear < 1780; ");
$q1780 = $db->prepare("SELECT count(*) AS count FROM document WHERE date = ? AND book = 1 AND lang = 'fre' AND posthum=1 AND birthyear >= 1780 AND birthyear < 1880; ");
$q1880 = $db->prepare("SELECT count(*) AS count FROM document WHERE date = ? AND book = 1 AND lang = 'fre' AND posthum=1 AND birthyear >= 1880 ; ");

for ($date=$from; $date <= $to; $date++) {

  // $qtot->execute(array($date));
  // $atot[] = current($qtot->fetch(PDO::FETCH_NUM)) ;
  $qtot->execute(array($date));
  $tot = current($qtot->fetch(PDO::FETCH_NUM)) ;
  $qpost->execute(array($date));
  $post = current($qpost->fetch(PDO::FETCH_NUM)) ;

  $qant->execute(array($date));
  $ant = current($qant->fetch(PDO::FETCH_NUM));

  $q500->execute(array($date));
  $f500 = current($q500->fetch(PDO::FETCH_NUM)) ;

  $q1450->execute(array($date));
  $f1450 = current($q1450->fetch(PDO::FETCH_NUM)) ;

  $q1600->execute(array($date));
  $f1600 = current($q1600->fetch(PDO::FETCH_NUM)) ;

  $q1690->execute(array($date));
  $f1690 = current($q1690->fetch(PDO::FETCH_NUM)) ;

  $q1780->execute(array($date));
  $f1780 = current($q1780->fetch(PDO::FETCH_NUM)) ;

  $q1880->execute(array($date));
  $f1880 = current($q1880->fetch(PDO::FETCH_NUM)) ;


  echo "  [".$date
    .", ".($tot - $post)
    .", ".$f1880
    .", ".$f1780
    .", ".$f1690
    .", ".$f1600
    .", ".$f1450
    .", ".$f500
    .", ".$ant
  ."],\n";
}
?>];

var attrs = {
  labels: [ "Année", "Vivants", "XXe","XIXe", "XVIIIe", "XVIIe", "Renaissance", "Moyen-Âge", "Antiquité" ],
  title : "Databnf, rééditions du patrimoine",
  ylabel: "Titres des morts",
  y2label: "Titres des vivants",
  showRoller: true,
  legend: "always",
  strokeWidth: 1,
  valueRange: [1, <?php echo $max ?>],
  stackedGraph: true,
  series: {
    "Antiquité": {
      color: "#00F",
    },
    "Moyen-Âge": {
      color: "#80F",
    },
    "Renaissance": {
      color: "#F08",
    },
    "XVIIe": {
      color: "#F00",
    },
    "XVIIIe": {
      color: "#F80",
    },
    "XIXe": {
      color: "#080",
    },
    "XXe": {
      color: "#008",
    },
    "Vivants": {
      color: "#666",
      strokeWidth: 4,
      axis: 'y2',
      strokePattern: [4, 1],
    },
  },
};
var annoteSeries = "Vivants";
<?php include('dygraph-common.php') ?>
g.ready(function() {
  var anns = g.annotations();
  g.setAnnotations(anns.concat([
    {series: "Vivants", x: "1981", shortText: "Prix unique du livre", width: "", height: "", cssClass: "annl"},
    {series: "Vivants", x: "2009", shortText: "Crise 2008", width: "", height: "", cssClass: "annl"},
  ]));
});
    </script>
    <div class="text">
    <p>Ce graphique projette les titres à leur date de publication, en fonction de la date de naissance de l’auteur principal (il n’est pas souvent possible de connaître la date de publication originale de l’œuvre, par exemple pour un dialogue de Platon ou une intégrale de Voltaire). Le découpage en siècles est toujours un peu arbitraire, toutefois, la tradition des histoires littéraires s’accorde assez avec les événements traumatiques qui tranchent dans les générations, comme les guerres de Religion, la fin de règne de Louis XIV, la Révolution, ou la Grande-Guerre. Les dates sont ajustées pour ne pas séparer des auteurs que l’on a coutume de ranger ensemble, comme les Lumières ou les Romantiques. Le Moyen-Âge commence très tôt, afin d’y inclure les pères de l’Église, qui forment un ensemble cohérent encore maintenant pour les éditions catholiques.</p>

    <ul>
    <li>Antiquité, né avant 150 ;</li>
    <li>Moyen-Âge, né entre 150 et 1450, à partir de Tertullien (155–222) ;</li>
    <li>Renaissance, né entre 1450 et 1600, à partir de Machiavel (1469–1527) ;</li>
    <li>XVIIe s., né entre 1600 et 1680, à partir de Corneille (1606–1684) ;</li>
    <li>XVIIIe s., né entre 1680 et 1780, à partir de Marivaux (1688–1763) ;</li>
    <li>XIXe s., né entre 1780 et 1880, à partir de Stendhal (1783–1842) ;</li>
    <li>XXe s., né après 1880, à partir d’Apollinaire (1880–1918).</li>
    </ul>

    <p>
    Une fois stabilisé, le nombre de titres d’un siècle varie assez peu, c’est-à-dire que le nombre de documents publiés d’un auteur du XVII<sup>e</sup> s. reste relativement stable au XIX<sup>e</sup> et au XX<sup>e</sup> s, même si le nombre d’autres titres publiés est 10 fois plus important. L‘espace supplémentaire est occupé par les nouveautés. La réédition des titres anciens est affectée par les guerres, comme les nouveautés, on observera le profil très particulier après 1945, où la réédition reprend beaucoup plus fort qu’après 1918, politique volontariste du Conceil National de la Résistance.</p>
    </div>
    <?php include (dirname(__FILE__).'/footer.php') ?>
  </body>
</html>
