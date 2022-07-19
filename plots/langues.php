<?php
// header('Content-type: text/plain; charset=utf-8');
include ( dirname(__FILE__).'/Cataviz.php' );
$db = new Cataviz( "databnf.sqlite" );
$from = 1910;
if (isset($_REQUEST['from'])) $from = $_REQUEST['from'];
if ( $from < 1475 ) $from = 1475;
if ( $from > 2015 ) $from = 2000;
$to = 2014;
if (isset($_REQUEST['to'])) $to = $_REQUEST['to'];
if ( $to < 1475 ) $to = 2014;
if ( $to > 2015 ) $to = 2014;
if ( isset($_REQUEST['smooth']) ) $smooth = $_REQUEST['smooth'];
else $smooth = 0;
if ( $smooth < 0 ) $smooth = 0;
if ( $smooth > 50 ) $smooth = 50;
$log = 0;
if ( isset( $_REQUEST['log'] ) ) $log = 0+$_REQUEST['log'];
$max = @$_REQUEST['max'];

?><!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <script src="lib/dygraph.min.js">//</script>
    <link rel="stylesheet" type="text/css" href="lib/dygraph.css"/>
    <link rel="stylesheet" type="text/css" href="cataviz.css"/>
    <style>
.dygraph-legend { left: 40% !important; }
    </style>
  </head>
  <body>
    <?php include ( dirname(__FILE__).'/menu.php' ) ?>
    <header>
      <div class="links">
        <a href="?">Data.bnf.fr, répartition des langues</a> |
        <a href="?from=1475&amp;to=1645&amp;smooth=5">1475-1645 montée du français</a> |
        <a href="?from=1620&amp;to=1830&amp;smooth=10&amp;max=350">1620-1830 baisse du latin</a> |
        <a href="?from=1780&amp;to=1980&amp;max=350&amp;smooth=2">1780-1980 conservation du latin</a> |
        <a href="?from=1830&amp;to=1990">1830-1990 taux d’erreur</a>
      </div>
      <form name="dates" style="z-index: 3; ">
        <button onclick="window.location.href='?'; " type="button">Reset</button>
        From <input name="from" size="4" value="<?php echo $from ?>"/>
        to <input name="to" size="4" value="<?php echo  $to ?>"/>
        <button type="submit">▶</button>
        Zoom
        <button type="button" onclick="var options = {}; var max = g.yAxisRange()[1] /1.5; options.valueRange = [ 1, max]; g.updateOptions(options); ">▼</button>
        <button type="button" onclick="var options = {}; var max = g.yAxisRange()[1] *1.5; options.valueRange = [ 1, max]; g.updateOptions(options); ">▲</button>
        Échelle
        <button id="log" type="button">log</button>
        <button id="linear" disabled="true" type="button">linéaire</button>
      </form>
    </header>
    <div id="chart" class="dygraph" style="width:100%; height:550px;"></div>
    <script type="text/javascript">
    g = new Dygraph(
      document.getElementById("chart"),
      [
<?php
// fre, eng, ger, ita, zxx ?, spa, lat, frm, ara, gre, chi
// part des documents avec un langue
$qtot = $db->prepare( "SELECT count(*) AS count FROM document WHERE date = ? AND type = 'Text' " );
$qfre = $db->prepare( "SELECT count(*) AS count FROM document WHERE date = ? AND type = 'Text' AND lang = 'fre' " );
$qnul = $db->prepare( "SELECT count(*) AS count FROM document WHERE date = ? AND type = 'Text' AND lang IS NULL " );
$qeng = $db->prepare( "SELECT count(*) AS count FROM document WHERE date = ? AND type = 'Text' AND lang = 'eng' " );
$qger = $db->prepare( "SELECT count(*) AS count FROM document WHERE date = ? AND type = 'Text' AND lang = 'ger' " );
$qita = $db->prepare( "SELECT count(*) AS count FROM document WHERE date = ? AND type = 'Text' AND lang = 'ita' " );
$qspa = $db->prepare( "SELECT count(*) AS count FROM document WHERE date = ? AND type = 'Text' AND lang = 'spa' " );
$qlat = $db->prepare( "SELECT count(*) AS count FROM document WHERE date = ? AND type = 'Text' AND lang = 'lat' " );
$qfrm = $db->prepare( "SELECT count(*) AS count FROM document WHERE date = ? AND type = 'Text' AND lang = 'frm' " );
$qara = $db->prepare( "SELECT count(*) AS count FROM document WHERE date = ? AND type = 'Text' AND lang = 'ara' " );
$qgre = $db->prepare( "SELECT count(*) AS count FROM document WHERE date = ? AND type = 'Text' AND lang = 'gre' " );
$qchi = $db->prepare( "SELECT count(*) AS count FROM document WHERE date = ? AND type = 'Text' AND lang = 'chi' " );
$qdut = $db->prepare( "SELECT count(*) AS count FROM document WHERE date = ? AND type = 'Text' AND lang = 'dut' " );
// $q2 = $db->prepare( "SELECT count(*) AS count FROM document WHERE date = ? AND lang != 'fre' AND lang IS NOT NULL " );
$adate = array();
$atot = array();
$afre = array();
$anul = array();
$alat = array();
$afrm = array();
$aita = array();
$aspa = array();
$aeng = array();
$ager = array();
for ( $date=$from; $date <= $to; $date++ ) {
  $adate[] = $date;

  // $qtot->execute( array( $date ) );
  // $atot[] = current( $qtot->fetch( PDO::FETCH_NUM ) ) ;
  $qfre->execute( array( $date ) );
  $afre[] = current( $qfre->fetch( PDO::FETCH_NUM ) ) ;
  $qfrm->execute( array( $date ) );
  $afrm[] = current( $qfrm->fetch( PDO::FETCH_NUM ) );

  $qnul->execute( array( $date ) );
  $anul[] = current( $qnul->fetch( PDO::FETCH_NUM ) ) ;
  $qlat->execute( array( $date ) );
  $alat[] = current( $qlat->fetch( PDO::FETCH_NUM ) );
  $qita->execute( array( $date ) );
  $aita[] = current( $qita->fetch( PDO::FETCH_NUM ) );

}
$size = count( $adate );
for ( $i=0; $i < $size; $i++ ) {
  echo "[".$adate[$i]
    .", ". ($afre[$i]+$afrm[$i])
    .", ".$alat[$i]
    .", ".$aita[$i]
    .", ".$anul[$i]
  ."],\n";
}
       ?>],
      {
        labels: [ "Année", "Français", "Latin", "Italien", "???" ],
        ylabel: "nb. de titres",
        labelsSeparateLines: "true",
        showRoller: true,
        rollPeriod: <?php echo $smooth ?>,
        legend: "always",
        strokeWidth: 3,
        logscale: <?php echo $log ?>,
        valueRange: [1, <?php echo $max ?>],
        series: {
          "Français": {
            color: "#330099",
          },
          "Latin": {
            color: "rgba(128, 128, 128, 0.4)",
            strokeWidth: 5,
          },
          "Italien": {
            color: "#339933",
            strokeWidth: 1,
          },
          Anglais: {
            color: "#FF3333",
            strokeWidth: 1,
          },
          Allemand: {
            color: "#882222",
            strokeWidth: 1,
          },
          "???": {
            strokePattern: [4, 4],
            color: "#666",
            strokeWidth: 2,
          },
          "Divers": {
            color: "#CCCCCC",
            strokeWidth: 1,
          },
        },
      }
    );
    g.ready(function() {
      g.setAnnotations([
        { series: "Latin", x: "1789", shortText: "1789", width: "", height: "", cssClass: "ann", },
        { series: "Latin", x: "1815", shortText: "1815", width: "", height: "", cssClass: "ann", },
        { series: "Latin", x: "1830", shortText: "1830", width: "", height: "", cssClass: "ann", },
        { series: "Latin", x: "1848", shortText: "1848", width: "", height: "", cssClass: "ann", },
        { series: "Latin", x: "1870", shortText: "1870", width: "", height: "", cssClass: "ann", },
        { series: "Latin", x: "1914", shortText: "1914", width: "", height: "", cssClass: "ann", },
        { series: "Latin", x: "1939", shortText: "1939", width: "", height: "", cssClass: "ann", },
      ]);
    });
    var linear = document.getElementById("linear");
    var log = document.getElementById("log");
    var setLog = function(val) {
      g.updateOptions({ logscale: val });
      linear.disabled = !val;
      log.disabled = val;
    };
    linear.onclick = function() { setLog(false); };
    log.onclick = function() { setLog(true); };
    </script>
    <?php include ( dirname(__FILE__).'/footer.php' ) ?>
  </body>
</html>
