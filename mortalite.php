<?php
$from = 1760;
$to = 1960;
include ( dirname(__FILE__).'/Cataviz.php' );
$db = new Cataviz( "databnf.sqlite" );
if ( !isset( $_REQUEST['books'] ) ) $books = 10;
else $books = $_REQUEST['books'];
if ( !$books || $books < 0 ) $books = 0;
$gender = @$_REQUEST['gender'];
if ( $gender != 1 && $gender != 2 ) $gender = null;


?><!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <title>Mortalité, Databnf.</title>
    <script src="lib/dygraph.min.js">//</script>
    <link rel="stylesheet" type="text/css" href="lib/dygraph.css"/>
    <link rel="stylesheet" type="text/css" href="cataviz.css"/>
    <style>
    .dygraph-legend { left: 65px !important; top: 40px !important; width: 22ex !important; }
    </style>
  </head>
  <body>
    <?php include ( dirname(__FILE__).'/menu.php' ) ?>
    <header>
      <div class="links">
        <a href="" target="_new">Auteurs français, mortalité et longévité</a> 
        | <a href="?from=1600&amp;to=2015&amp;log=1">4 siècles</a>
        | <a href="?from=1760&amp;to=1860">Révolutions</a>
        | <a href="?from=1860&amp;to=2020&amp;log=1">XX<sup>e</sup></a>
      </div>
      <form name="dates">
        <button onclick="window.location.href='?'; " type="button">Reset</button>
        De <input name="from" size="4" value="<?php echo $from ?>"/>
        à <input name="to" size="4" value="<?php echo  $to ?>"/>
        <label>Sexe
          <select name="gender" onchange="this.form.submit()">
            <option value=""/>
            <option value="2" <?php if ($gender==2) echo ' selected="selected"' ?>>Femmes</option>
            <option value="1" <?php if ($gender==1) echo ' selected="selected"' ?>>Hommes</option>
          </select>
        </label>
        <label title="Nombre de livre minimum que doit avoir signé l’auteur">Seuil livres <input name="books" size="4" value="<?php echo $books ?>"/></label>
        Échelle
        <button id="log" <?php if( $log ) echo'disabled="true"';?> type="button">log</button>
        <button id="linear" <?php if( !$log ) echo'disabled="true"';?> type="button">linéaire</button>
        <button type="submit">▶</button>
      </form>
    </header>
    <div id="chart" class="dygraph"></div>
    <script type="text/javascript">
    g = new Dygraph(
      document.getElementById("chart"),
      [
<?php

// pas de moyenne pour ces dates
$guerres = [ 1789, 1790, 1791, 1792, 1793, 1794,  1870, 1871, 1914, 1915, 1916, 1917, 1918, 1939, 1940, 1941, 1942, 1943, 1944, 1945 ];
$guerres = array_flip( $guerres );

if ( $gender ) {
  $docsq = $db->prepare( "SELECT count(*), avg(age) FROM person WHERE fr = 1 AND deathyear >= ? AND deathyear <= ? AND gender = ?" );
  $booksq = $db->prepare( "SELECT count(*), avg(age) FROM person WHERE fr = 1 AND deathyear >= ? AND deathyear <= ? AND books > ? AND gender = ? " );
}
else {
  $docsq = $db->prepare( "SELECT count(*), avg(age) FROM person WHERE fr = 1 AND deathyear >= ? AND deathyear <= ?" );
  $booksq = $db->prepare( "SELECT count(*), avg(age) FROM person WHERE fr = 1 AND deathyear >= ? AND deathyear <= ? AND books > ?" );
}

$lastagebooks = 50;
for ( $date=$from; $date <= $to; $date++ ) {

  if ( $gender == 2 ) {
    $delta = 10;
    if ( $date >= 1600 ) $delta = 6;
    if ( $date >= 1700 ) $delta = 5;
    if ( $date >= 1789 ) $delta = 4;
    if ( $date >= 1900 ) $delta = 3;
    if ( isset( $guerres[$date] ) ) $delta = 0;
  }
  else {
    $delta = 5;
    if ( $date >= 1600 ) $delta = 4;
    if ( $date >= 1700 ) $delta = 3;
    if ( $date >= 1789 ) $delta = 2;
    if ( $date >= 1900 ) $delta = 1;
    if ( isset( $guerres[$date] ) ) $delta = 0;
  }

  $countdocs = $agedocs = 0;
  if ( $gender ) $docsq->execute( array( $date-$delta, $date+$delta, $gender ) );
  else $docsq->execute( array( $date-$delta, $date+$delta ) );
  list ( $countdocs, $agedocs ) = $docsq->fetch( PDO::FETCH_NUM );
  $countdocs = $countdocs/(1+2*$delta);
  if ( $books ) {
    $countbooks = $agebooks = 0;
    if ( $gender == 2 ) $delta = 1+$delta;
    if ( $gender ) $booksq->execute( array( $date-$delta, $date+$delta, $books, $gender ) );
    else $booksq->execute( array( $date-$delta, $date+$delta, $books ) );
    list ( $countbooks, $agebooks ) = $booksq->fetch( PDO::FETCH_NUM );
    $countbooks = $countbooks/(1+2*$delta);
    if ( !$agebooks ) $agebooks = $lastagebooks;
    else $lastagebooks = $agebooks;
  }

  echo "[".$date;
  echo ",". number_format( $countdocs, 2, '.', '');
  echo ",". number_format( $agedocs, 2, '.', '');
  if ( $books ) {
    echo ",". number_format( $countbooks, 2, '.', '');
    echo ",". number_format( $agebooks, 2, '.', '');
  }
  echo "],\n";
}
       ?>],
      {
        title : "Databnf<?php if( $gender == 1) { echo ", hommes"; } else if( $gender == 2) { echo ", femmes"; } ?>, mortalité — effectifs et âge à la date de mort",
        titleHeight: 35,
        labels: [ "Année",
          "Morts", "Âge", <?php if ($books) echo " \"Morts > $books livres\", \"Âge > $books livres\", "?>
        ],
        legend: "always",
        labelsSeparateLines: "true",
        ylabel: "Nombre de morts",
        y2label: "Âge à la mort",
        // showRoller: true,
        // rollPeriod: <?php echo $smooth ?>,
        <?php if ($log) echo "logscale: true,";  ?>
        series: {
          "Morts": {
            color: "rgba( 128, 192, 0, 0.7 )",
            fillGraph: true,
            strokePattern: [4,4],
            strokeWidth: 0.5,
          },
          "Âge": {
            axis: 'y2',
            color: "rgba( 128, 192, 0, 0.7 )",
            strokeWidth: 4,
          },
          "Morts > <?=$books?> livres" : {
            color: "rgba( 0, 128, 0, 0.7 )",
            fillGraph: true,
            strokePattern: [4,4],
            strokeWidth: 0.5,
          },
          "Âge > <?=$books?> livres" : {
            color: "rgba( 0, 128, 0, 0.7 )",
            strokeWidth: 4,
            axis: 'y2',
          },
        },
        axes: {
          x: {
            drawGrid: true,
            independentTicks: true,
            gridLineColor: "rgba( 128, 128, 128, 0.5)",
            gridLineWidth: 0.5,
          },
          y: {
            independentTicks: true,
            drawGrid: false,
            gridLineColor: "rgba( 128, 128, 128, 0.5)",
            gridLineWidth: 1,
          },
          y2: {
            independentTicks: true,
            drawGrid: true,
            gridLineColor: "rgba( 192, 128, 160, 1 )",
            gridLineWidth: 1,
          },
        },
        underlayCallback: function(canvas, area, g) {
          canvas.fillStyle = "rgba(192, 192, 192, 0.2)";
          var periods = [ [1789,1794], [1814,1815], [1830,1831], [1848,1849], [1870,1871], [1914,1918], [1939,1945]];
          var lim = periods.length;
          for ( var i = 0; i < lim; i++ ) {
            var bottom_left = g.toDomCoords( periods[i][0], -20 );
            var top_right = g.toDomCoords( periods[i][1], +20 );
            var left = bottom_left[0];
            var right = top_right[0];
            canvas.fillRect(left, area.y, right - left, area.h);
          }
        },
      }
    );
    g.ready(function() {
      g.setAnnotations([
        { series: "Morts", x: "1648", shortText: "La Fronde", width: "", height: "", cssClass: "annl", },
        { series: "Morts", x: "1789", shortText: "1789", width: "", height: "", cssClass: "annl", },
        { series: "Morts", x: "1815", shortText: "1815", width: "", height: "", cssClass: "annl", },
        { series: "Morts", x: "1830", shortText: "1830", width: "", height: "", cssClass: "annl", },
        { series: "Morts", x: "1848", shortText: "1848", width: "", height: "", cssClass: "annl", },
        { series: "Morts", x: "1870", shortText: "1870", width: "", height: "", cssClass: "annl", },
        { series: "Morts", x: "1914", shortText: "1914", width: "", height: "", cssClass: "annl", },
        { series: "Morts", x: "1939", shortText: "1939", width: "", height: "", cssClass: "annl", },
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
    <div class="text">
    <p>Pour chaque année, ce graphique projette les auteurs francophones d’un livre (plus de 50 pages) à leur date de mort, avec leur âge à la mort. </p>
    </div>
    <?php include ( dirname(__FILE__).'/footer.php' ) ?>
  </body>
</html>
