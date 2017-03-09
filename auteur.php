<?php
include ( dirname(__FILE__).'/Cataviz.php' );
$db = new Cataviz( "databnf.sqlite" );
$name = "";
if ( isset( $_REQUEST['persark'] ) ) {
  $persark=$_REQUEST['persark'];
  $person = $db->person( $persark );
  if ( $person != null ) $name = $db->perstitle( $persark );
}

?><!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <script src="dygraph-combined.js">//</script>
    <link rel="stylesheet" type="text/css" href="cataviz.css"/>
  </head>
  <body>
    <?php include ( dirname(__FILE__).'/menu.php' ) ?>
    <p/>
    <form name="search">
      <label><span>Auteur</span>
          <input id="persark" name="persark" type="hidden" value="<?php echo $persark ?>"/>
          <input id="name" name="name" placeholder="Auteur ?" value="<?php echo $db->perstitle( $persark ) ?>" size="30"/>
          <select id="perslist" size="8">
          </select>
          <button type="submit">▶</button>
        </div>
      </label>
    </form>
<?php if ( isset( $person ) ) { ?>
    <p><?php echo '[<a target="_new" href="http://catalogue.bnf.fr/ark:/12148/'.$person['ark'].'">notice BNF</a>] '. $person['note'] ?></p>
    <p>Auteur principal de <b><?php echo $person['docs'] ?></b> documents enregistrés par data.bnf.fr, distribution des publications par année (courbe fine à points), et approximation du stock (courbe large sans points). La courbe des publications permet d’observer l’actualité éditoriale d’un auteur, la courbe du stock permet de se faire une idée du nombre de titres en circulation. Cet “amortissement” est calculé sur 30 ans. Chaque nouvelle publication s’ajoute à la somme des précédentes, chaque année un livre perd 1/30 ème de sa valeur. Cette courbe évite d’interpréter une absence de nouvelles publications sur 10 ou 20 ans comme une disparition complète d’un auteur, qui reste disponible dans les bibliothèques.</p>

    <div id="chart" class="dygraph" style="width:100%; height:300px;"></div>
    <script type="text/javascript">
  g = new Dygraph(
    document.getElementById("chart"),
    <?php echo $db->dygraph( $persark ); ?>,
    {
      labels: [ "Année", "Publications", "Stock" ],
      ylabel: 'Publications',
      y2label: 'Stock',
      series: {
        Publications: {
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
if ($a40) echo '{ series: "Publications", x:'.$a40.', tickHeight: 40, shortText: "40 ans", width: "", height: "", cssClass: "ann" },'."\n";
       ?>

    ]);
  });
</script>

<p>Voir aussi, le <a href="relations.php?persark=<?php echo $persark ?>">réseau des relations</a>, ou la <a href="biblio.php?persark=<?php echo $persark ?>">bibliographie</a>.</p>

<p>Bibliographie établie automatiquement à partir des <a href="http://data.bnf.fr/liste-oeuvres" target="_blank">notices d’œuvres</a> de la BNF. Elle peut être significative de l’histoire éditoriale d’un auteur, mais elle ne sera pas exhaustive, ni des œuvres, ni des rééditions.</p>
    <?php

echo $db->editions( $persark );


    ?>
<?php } ?>
    <?php include ( dirname(__FILE__).'/footer.php' ) ?>
    <script type="text/javascript" src="Sortable.js">//</script>
    <script type="text/javascript" src="forms.js">//</script>
  </body>
</html>
<?php

?>
