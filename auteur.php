<?php
include ( dirname(__FILE__).'/Cataviz.php' );
$db = new Cataviz( "databnf.sqlite" );
if ( isset( $_REQUEST['person'] ) ) $persark=$_REQUEST['person'];
else $persark = "cb11888978p"; // Apollinaire
$person = $db->person( $persark );

?><!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <script src="dygraph-combined.js">//</script>
    <link rel="stylesheet" type="text/css" href="cataviz.css"/>
  </head>
  <body>
    <?php include ( dirname(__FILE__).'/menu.php' ) ?>
      <h1><?php echo $db->perstitle( $persark ) ?></h1>
    <p><?php echo '[<a target="_new" href="http://catalogue.bnf.fr/ark:/12148/'.$person['ark'].'">notice BNF</a>] '. $person['note'] ?></p>

    <div id="chart" class="dygraph" style="width:100%; height:300px;"></div>
    <script type="text/javascript">
  g = new Dygraph(
    document.getElementById("chart"),
    <?php echo $db->dygraph( $persark ); ?>,
    {
      labels: [ "Année", "Sorties", "Stock" ],
      ylabel: 'Sorties',
      y2label: 'Stock',
      series: {
        Sorties: {
          axis: 'y1',
          drawPoints: true,
          pointSize: 3,
          color: "rgba( 0, 0, 0, 0.4)",
          strokeWidth: 0.5,
        },
        Stock: {
          axis: 'y2',
          color: "rgba( 128, 128, 128, 0.3)",
          strokeWidth: 10,
        },
      }
    }
  );
  g.ready(function() {
    g.setAnnotations([
      <?php
if ($person['deathyear']) echo '{ series: "Stock", x:'.$person['deathyear'].', tickHeight: 40, shortText: "Mort", width: "", height: "", cssClass: "ann" },'."\n";
$a40 = $person['birthyear'] + 40;
if ( $person['deathyear'] && $a40 >= $person['deathyear']) $a40=null;
if ($a40) echo '{ series: "Sorties", x:'.$a40.', tickHeight: 40, shortText: "40 ans", width: "", height: "", cssClass: "ann" },'."\n";
       ?>

    ]);
  });
</script>
<?php
echo '<p>'.$person['name'].', auteur de '.$person['docs'].' documents dans data.bnf.fr, distribution des sorties par année, et approximation du stock (amortissement sur 30 ans)</p>';
?>

    <?php
echo '<p>Ouvrir le graphe de <a href="relations.php?person='.$persark.'" target="_new">relations</a></p>';
echo $db->editions( $persark );

echo '<iframe name="biblio" src="biblio.php?person='.$persark.'" width="100%" height="100%"></iframe>';

    ?>
    <script type="text/javascript" src="Sortable.js">//</script>
  </body>
</html>
<?php

?>
