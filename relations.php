<?php
include ( dirname(__FILE__).'/Cataviz.php' );
$db = new Cataviz( "databnf.db" );
if ( isset( $_REQUEST['person'] ) ) $perscode=$_REQUEST['person'];
else $perscode = "cb11888978p"; // Apollinaire
$pers = $db->person( $perscode );
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
    <script src="Cataviz.js">//</script>
    <link rel="stylesheet" type="text/css" href="cataviz.css"/>
  </head>
  <body style="margin:0; padding: 0; ">
    <div id="cataviz" class="graph" oncontextmenu="return false" style="position: relative">
      <div style="position: absolute; bottom: 0; right: 2px; z-index: 2; ">
        <button class="shot but" type="button" title="Prendre une photo">📷</button>
        <button class="zoomin but" style="cursor: zoom-in; " type="button" title="Grossir">+</button>
        <button class="zoomout but" style="cursor: zoom-out; " type="button" title="Diminuer">-</button>
        <button class="mix but" type="button" title="Mélanger le graphe">♻</button>
        <button class="grav but" type="button" title="Démarrer ou arrêter la gravité">►</button>
        <span class="resize interface" style="cursor: se-resize; font-size: 1.3em; " title="Redimensionner la feuille">⬊</span>
      </div>
      <form name="visu" style="position: absolute; top: 10px; left: 10px; z-index: 2;" action="#cataviz">
        <?php echo '<a href="?person='.$perscode.'">'.$pers['name'].'</a>' ?>
        <input name="person" type="hidden" value="<?php echo $perscode; ?>"/>
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
    </div>
    <script id="data"> (function () {
      var data = <?php echo $db->sigma( $perscode, $from, $to, $role ); ?>;
      var graph = new Cataviz( "cataviz", data );
    })();
    </script>

  </body>
</html>
