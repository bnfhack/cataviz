<?php
include ( dirname(__FILE__).'/Cataviz.php' );
$db = new Cataviz( "databnf.sqlite" );
if ( isset( $_REQUEST['persark'] ) ) $persark=$_REQUEST['persark'];
else $persark = "cb11888978p"; // Apollinaire
$pers = $db->person( $persark );
if ( isset($_REQUEST['from']) ) $from = $_REQUEST['from'];
else $from = $pers['birthyear'];
if ( isset($_REQUEST['to']) ) $to = $_REQUEST['to'];
else $to = $pers['deathyear'];
$role = @$_REQUEST['role'];


?><!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <script src="sigma/sigma.min.js">//</script>
    <script src="sigma/sigma.layout.forceAtlas2.min.js">//</script>
    <script src="sigma/sigma.plugins.dragNodes.min.js">//</script>
    <script src="sigma/sigma.exporters.image.min.js">//</script>
    <script src="catagraph.js">//</script>
    <link rel="stylesheet" type="text/css" href="cataviz.css"/>
    <script>
#perslist { margin-left: 0;}
    </script>
  </head>
  <body>
    <?php include ( dirname(__FILE__).'/menu.php' ) ?>
    <form name="visu" style="z-index: 2;" action="#cataviz">
      <input id="persark" name="persark" type="hidden" value="<?php echo $persark ?>"/>
      <input id="name" name="name" placeholder="Auteur ?" value="<?php echo $db->perstitle( $persark ) ?>" size="30"/>
      <select id="perslist" size="8">
      </select>
      De <input name="from" size="4" placeholder="De" value="<?php echo $from; ?>"/>
      à <input name="to" size="4"  placeholder="À" value="<?php echo $to; ?>"/>
      rôles <select name="role" onchange="this.form.submit()">
        <option></option>
        <option value="auteur" <?php if( "auteur" ==$role ) echo ' selected="selected"' ?>>Auteurs</option>
        <option value="edition" <?php if( "edition" ==$role ) echo ' selected="selected"' ?>>Éditeurs</option>
        <option value="traduction" <?php if( "traduction" ==$role ) echo ' selected="selected"' ?>>Traducteurs</option>
        <option value="musique" <?php if( "musique" ==$role ) echo ' selected="selected"' ?>>Musiciens</option>
        <option value="illustration" <?php if( "illustration" ==$role ) echo ' selected="selected"' ?>>Illustrateurs</option>
        <option value="spectacle" <?php if( "spectacle" ==$role ) echo ' selected="selected"' ?>>Spectacle</option>
      </select>
      <button type="submit">Filtrer</button>
    </form>
    <div id="cataviz" class="graph" oncontextmenu="return false; " style="position: relative; width: 100%; height: 90%;  ">
      <div style="position: absolute; bottom: 0; right: 2px; z-index: 2; ">
        <button class="shot but" type="button" title="Prendre une photo">📷</button>
        <button class="zoomin but" style="cursor: zoom-in; " type="button" title="Grossir">+</button>
        <button class="zoomout but" style="cursor: zoom-out; " type="button" title="Diminuer">-</button>
        <button class="mix but" type="button" title="Mélanger le graphe">♻</button>
        <button class="grav but" type="button" title="Démarrer ou arrêter la gravité">►</button>
        <span class="resize interface" style="cursor: se-resize; font-size: 1.3em; " title="Redimensionner la feuille">⬊</span>
      </div>
    </div>
    <p>
Ce réseau est construit autmatiquement à partir des collaborations enregistrées pour un même document.
Le nœud central (en rouge) représente l’auteur sur lequel s’effectue la requête.
Les nœuds rayonnants de premier niveau (en violet) représentent les documents auquel l’auteur principal a contribué avec d’autres personnes.
Les nœuds de deuxième niveau (en gris) représentent les personnes ayant collaboré à un document avec l’auteur principal.
Seuls les documents datés apparaissent.
    </p>
    <script id="data"> (function () {
      var data = <?php echo $db->sigma( $persark, $from, $to, $role ); ?>;
      var graph = new Cataviz( "cataviz", data );
    })();
    </script>
    <?php include ( dirname(__FILE__).'/footer.php' ) ?>
    <script type="text/javascript" src="forms.js">//</script>
  </body>
</html>
