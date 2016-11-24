<?php
// header('Content-type: text/plain; charset=utf-8');
include ( dirname(__FILE__).'/Cataviz.php' );
$db = new Cataviz( "databnf.db" );
$from = @$_REQUEST['from'];
if ( $from < 1475 ) $from = 1475;
if ( $from > 2015 ) $from = 2000;
$to = @$_REQUEST['to'];
if ( $to < 1475 ) $to = 2015;
if ( $to > 2015 ) $to = 2015;


?><!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <script src="dygraph-combined.js">//</script>
    <link rel="stylesheet" type="text/css" href="cataviz.css"/>
    <style>
    </style>
  </head>
  <body>
    <?php include ( dirname(__FILE__).'/menu.php' ) ?>
    <div style="float: right;">
      Scale
      <button id="linear" disabled="true">linear</button>
      <button id="log">log</button>
    </div>
    <h1>Canon and archive, the catalog is not democratic (a zipfian distribution)</h1>
    <p>A few authors wrote a lot of the documents edited and re-edited; lots of authors wrote only one or two documents. The catalog is highly uneqal, and exactly follow a <a href="https://en.wikipedia.org/wiki/Zipf's_law">Zipf progression</a> (click log scale), like words of a corpus.</p>
    <div id="chart" class="dygraph" style="width:100%; height:600px;"></div>
    <script type="text/javascript">
    g = new Dygraph(
      document.getElementById("chart"),
      [
<?php
$sql = "SELECT docs FROM person WHERE birthyear >= ? AND birthyear <= ? ORDER BY docs DESC LIMIT 10000;";
$qzipf = $db->prepare( $sql );
$qzipf->execute( array( $from, $to ) );
$rank = 1;
while ( $row = $qzipf->fetch( PDO::FETCH_ASSOC ) ) {
  echo "[".$rank.",".$row['docs']."],\n";
  $rank++;
}
       ?>],
      {
        labels: [ "Rang", "Documents" ],
        legend: "follow",
        xlabel: "Author rank",
        ylabel: "Number of documents",
        series: {
          Documents: {
            drawPoints: true,
            pointSize: 5,
            color: "rgba( 255, 0, 0, 0.6)",
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
    <p>Données <a href="http://data.bnf.fr/semanticweb">data.bnf.fr</a> (avril 2016).</p>
  </body>
</html>
