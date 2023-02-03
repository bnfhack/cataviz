<?php
// header('Content-type: text/plain; charset=utf-8');
include (dirname(__FILE__).'/Cataviz.php');
$db = new Cataviz("databnf.sqlite");


?><!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <title>Zipf, le canon et l'archive</title>
    <script src="lib/dygraph.min.js">//</script>
    <link rel="stylesheet" type="text/css" href="lib/dygraph.css"/>
    <link rel="stylesheet" type="text/css" href="cataviz.css"/>
    <style>
    </style>
  </head>
  <body>
    <?php include (dirname(__FILE__).'/menu.php') ?>
    <div style="float: right;">
      Échelle
      <button id="log" <?php if($log) echo'disabled="true"';?> type="button">log</button>
      <button id="linear" <?php if(!$log) echo'disabled="true"';?> type="button">linéaire</button>
    </div>
    <h1>Le canon et l'archive, rééditions et hiérarchie des auteurs</h1>
    <p>
      Quelques auteurs sont édités et réédités, beaucoup d'auteurs ne signent que un ou deux documents.
      En <a href="?log=scale">échelle</a> logarithmique, le rapport
      entre le nombre de documents signés d'un auteur, et son rang selon le nombre de documents qu'il signe,
      suit exactement <a href="https://en.wikipedia.org/wiki/Zipf's_law">la droite de Zipf</a>.
    </p>
    <div id="chart" class="dygraph" style="width:100%; height:600px;"></div>
    <script type="text/javascript">
    g = new Dygraph(
      document.getElementById("chart"),
      [
<?php
$sql = "SELECT docs FROM person WHERE birthyear >= ? AND birthyear <= ? ORDER BY docs DESC LIMIT 10000;";
$qzipf = $db->prepare($sql);
$qzipf->execute(array($from, $to));
$rank = 1;
while ($row = $qzipf->fetch(PDO::FETCH_ASSOC)) {
  echo "[".$rank.",".$row['docs']."],\n";
  $rank++;
}
       ?>],
      {
        labels: [ "Rang", "Documents" ],
        legend: "follow",
        xlabel: "Auteur, rang",
        ylabel: "Documents, nombre",
        <?php if ($log) echo "logscale: true,";  ?>
        <?php if ($log) echo "axes: {x: {logscale: true}}, ";  ?>
        series: {
          Documents: {
            drawPoints: true,
            pointSize: 5,
            color: "rgba(255, 0, 0, 0.6)",
            strokeWidth: 0.5,
          },
        }
      }
   );
    var setLog = function(val) {
      g.updateOptions({ logscale: val, axes: { x: {  logscale: val } } });
      linear.disabled = !val;
      log.disabled = val;
    };
    var linear = document.getElementById("linear");
    var log = document.getElementById("log");
    linear.onclick = function() { setLog(false); };
    log.onclick = function() { setLog(true); };
    </script>
    <?php include (dirname(__FILE__).'/footer.php') ?>
  </body>
</html>
