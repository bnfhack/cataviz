<?php
$from = 1770;
$to = 1970;
$datemax = 1960;
include (dirname(__FILE__).'/Cataviz.php');
$db = new Cataviz("databnf.sqlite");
$gender = @$_REQUEST['gender'];
if ($gender != 1 && $gender != 2) $gender = null;

?><!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <title>Âges à la publication, Databnf</title>
    <script src="lib/dygraph.min.js">//</script>
    <link rel="stylesheet" type="text/css" href="lib/dygraph.css"/>
    <link rel="stylesheet" type="text/css" href="cataviz.css"/>
    <style>
    .dygraph-legend { left: 65px !important; top: 40px !important; width: 20ex !important; }
    </style>
  </head>
  <body>
    <?php include (dirname(__FILE__).'/menu.php') ?>
    <header>
      <div class="links">
        <a href="?">Générations</a>
        | <a href="?from=1760&amp;to=1970">2 siècles</a> 
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
var data = [
<?php
$guerres = [ 1789, 1790, 1791, 1792, 1793, 1794, 1870, 1871, 1914, 1915, 1916, 1917, 1918, 1939, 1940, 1941, 1942, 1943, 1944, 1945, 1946 ];
$guerres = array_flip($guerres);

// prendre les décennies
if ($gender) {
  $firstq = $db->prepare("SELECT avg(age1) FROM person WHERE fr = 1 AND gender=? AND opus1 >= ? AND opus1 <= ? ");
  $decq = $db->prepare("SELECT agedec, COUNT(*) AS count FROM document WHERE book = 1 AND lang = 'fre' AND posthum=0 AND gender = ? AND date >= ? AND date <= ? GROUP BY agedec ORDER BY agedec");
}
else {
  $firstq = $db->prepare("SELECT avg(age1) FROM person WHERE fr = 1 AND opus1 >= ? AND opus1 <= ? ");
  $decq = $db->prepare("SELECT agedec, COUNT(*) AS count FROM document WHERE book = 1 AND lang = 'fre' AND posthum=0 AND date >= ? AND date <= ? GROUP BY agedec  ORDER BY agedec");
}


for ($date=$from; $date <= $to; $date++) {
  echo "  [".$date;

  $delta = 0;
  $delta1 = 2;
  if ($gender == 2) {
    $delta1 = 8;
    $delta = 15;
    if ($date >= 1700) $delta = 6;
    if ($date >= 1780) $delta = 3;
    if ($date >= 1860) $delta = 2;
    if (isset($guerres[$date])) $delta = 0;
  }
  else {
    $delta = 2;
    $delta1 = 4;
    if ($date >= 1700) { $delta = 1; $delta1 = 2; }
    if ($date >= 1800) { $delta = 0; }
    if (isset($guerres[$date])) $delta = 0;
  }

  if ($gender) {
    $decq->execute(array($gender, $date-$delta, $date+$delta));
    $firstq->execute(array($gender, $date-$delta1, $date+$delta1));
  }
  else  {
    $decq->execute(array($date-$delta, $date+$delta));
    $firstq->execute(array($date-$delta1, $date+$delta1));
  }
  list($first) = $firstq->fetch(PDO::FETCH_NUM);
  echo ", ".number_format($first, 2, '.', '');
  // alimenter un tableau pour être sûr d’avoir le bon nombre de colonnes
  $dec = array(100=>0, 90=>0, 80=>0, 70=>0, 60=>0, 50=>0, 40=>0, 30=>0, 20=>0, 10=>0);
  while ($row = $decq->fetch(PDO::FETCH_NUM)) {
    if ($row[0] === null || $row[0] == 0) continue;
    $dec[$row[0]] = $row[1];
  }
  $sum = array_sum($dec);
  foreach ($dec as $key => $value) {
    if(!$sum) echo ", 0";
    else echo ", ".number_format(100.0*$value/$sum, 2, '.', '');
  }

  echo "],\n";
}?>
];
var attrs = {
  title : "Databnf<?php if($gender == 1) { echo ", hommes"; } else if($gender == 2) { echo ", femmes"; } ?>, âge de l'auteur principal à la date de publication.",
  labels: [ "Année", "Premier livre", "100–…", "90–99", "80–89", "70–79", "60–69", "50–59", "40–49", "30–39", "20–29", "10–19" ],
  y2label: "Âge au premier livre",
  ylabel: "% de livres",
  stackedGraph: true,
  series: {
    "Premier livre":{
      stackedGraph: false,
      axis: 'y2',
      color: "rgba(128, 128, 128, 1)",
      fillGraph: false,
      strokeWidth: 4,
      // strokePattern: [4,4],
    },
    "10–19":{ color: "rgba(192, 192, 192, 1)", fillGraph: true, strokeWidth: 0.5 },
    "20–29":{ color: "rgba(192, 192, 64, 1)", fillGraph: true, strokeWidth: 0.5 },
    "30–39":{ color: "rgba(64, 128, 64, 1)", fillGraph: true, strokeWidth: 0.5 },
    "40–49":{ color: "rgba(64, 192, 128, 1)", fillGraph: true, strokeWidth: 0.5 },
    "50–59":{ color: "rgba(64, 128, 64, 1)", fillGraph: true, strokeWidth: 0.5 },
    "60–69":{ color: "rgba(192, 192, 64, 1)", fillGraph: true, strokeWidth: 0.5 },
    "70–79":{ color: "rgba(192, 192, 192, 1)", fillGraph: true, strokeWidth: 0.5 },
    "80–89":{ color: "rgba(128, 128, 128, 1)", fillGraph: true, strokeWidth: 0.5 },
    "90–99":{ color: "rgba(64, 64, 64, 1)", fillGraph: true, strokeWidth: 0.5 },
    "100–…":{ color: "rgba(0, 0, 0, 1)", fillGraph: true, strokeWidth: 0.5 },

  },
};
var annoteSeries = "20–29";
<?php include('dygraph-common.php') ?>
g.updateOptions({legend: null, axes: {y: {valueRange: [0,101]}}});
g.ready(function() {
  var anns = g.annotations();
  g.setAnnotations(anns.concat([

    { series: "30–39", x: "1601", shortText: "30–39", width: "", height: "", cssClass: "annb", },
    { series: "40–49", x: "1601", shortText: "40–49", width: "", height: "", cssClass: "annb", },
    { series: "50–59", x: "1601", shortText: "50–59", width: "", height: "", cssClass: "annb", },
    { series: "60–69", x: "1601", shortText: "60–69", width: "", height: "", cssClass: "annb", },
    { series: "30–39", x: "1651", shortText: "30–39", width: "", height: "", cssClass: "annb", },
    { series: "40–49", x: "1651", shortText: "40–49", width: "", height: "", cssClass: "annb", },
    { series: "50–59", x: "1651", shortText: "50–59", width: "", height: "", cssClass: "annb", },
    { series: "60–69", x: "1651", shortText: "60–69", width: "", height: "", cssClass: "annb", },
    { series: "30–39", x: "1701", shortText: "30–39", width: "", height: "", cssClass: "annb", },
    { series: "40–49", x: "1701", shortText: "40–49", width: "", height: "", cssClass: "annb", },
    { series: "50–59", x: "1701", shortText: "50–59", width: "", height: "", cssClass: "annb", },
    { series: "60–69", x: "1701", shortText: "60–69", width: "", height: "", cssClass: "annb", },
    { series: "30–39", x: "1751", shortText: "30–39", width: "", height: "", cssClass: "annb", },
    { series: "40–49", x: "1751", shortText: "40–49", width: "", height: "", cssClass: "annb", },
    { series: "50–59", x: "1751", shortText: "50–59", width: "", height: "", cssClass: "annb", },
    { series: "60–69", x: "1751", shortText: "60–69", width: "", height: "", cssClass: "annb", },
    { series: "30–39", x: "1801", shortText: "30–39", width: "", height: "", cssClass: "annb", },
    { series: "40–49", x: "1801", shortText: "40–49", width: "", height: "", cssClass: "annb", },
    { series: "50–59", x: "1801", shortText: "50–59", width: "", height: "", cssClass: "annb", },
    { series: "60–69", x: "1801", shortText: "60–69", width: "", height: "", cssClass: "annb", },
    { series: "30–39", x: "1851", shortText: "30–39", width: "", height: "", cssClass: "annb", },
    { series: "40–49", x: "1851", shortText: "40–49", width: "", height: "", cssClass: "annb", },
    { series: "50–59", x: "1851", shortText: "50–59", width: "", height: "", cssClass: "annb", },
    { series: "60–69", x: "1851", shortText: "60–69", width: "", height: "", cssClass: "annb", },
    { series: "30–39", x: "1901", shortText: "30–39", width: "", height: "", cssClass: "annb", },
    { series: "40–49", x: "1901", shortText: "40–49", width: "", height: "", cssClass: "annb", },
    { series: "50–59", x: "1901", shortText: "50–59", width: "", height: "", cssClass: "annb", },
    { series: "60–69", x: "1901", shortText: "60–69", width: "", height: "", cssClass: "annb", },
    { series: "30–39", x: "1951", shortText: "30–39", width: "", height: "", cssClass: "annb", },
    { series: "40–49", x: "1951", shortText: "40–49", width: "", height: "", cssClass: "annb", },
    { series: "50–59", x: "1951", shortText: "50–59", width: "", height: "", cssClass: "annb", },
    { series: "60–69", x: "1951", shortText: "60–69", width: "", height: "", cssClass: "annb", },
    { series: "50–59", x: "2001", shortText: "50–59", width: "", height: "", cssClass: "annb", },
    { series: "60–69", x: "2001", shortText: "60–69", width: "", height: "", cssClass: "annb", },
    { series: "70–79", x: "2001", shortText: "70–79", width: "", height: "", cssClass: "annb", },
    { series: "80–89", x: "2001", shortText: "80–89", width: "", height: "", cssClass: "annb", },
  ]));
});
    </script>
    <div class="text">
    <p>Ce graphique projette l'âge de l'auteur principal à la date de publication d'un livre, pour établir la proportion des générations. Attention, les chiffres sont de moins en moins fiables au fur et à mesure que l'on s'approche du présent,
    car les dates de naissance ne sont pas encore renseignées pour les auteurs récents
    (voir la projection des auteurs à leur date de naissance, la <a href="natalite.php?from=1900&to=2016&books=10">«natalité»</a>).
    Cette vue est une sorte de pyramide des âges montrant une grande importance des auteurs les plus vieux,
    longtemos réédités de leur vivant. L'âge moyen au premier livre est une indication du renouvellement des générations littéraires.
  </p>
    </div>
    <?php include (dirname(__FILE__).'/footer.php') ?>
  </body>
</html>
