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
    <script src="lib/dygraph.min.js">//</script>
    <link rel="stylesheet" type="text/css" href="lib/dygraph.css"/>
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
<?php } ?>
    <?php include ( dirname(__FILE__).'/footer.php' ) ?>
    <script type="text/javascript" src="forms.js">//</script>
  </body>
</html>
<?php

?>
