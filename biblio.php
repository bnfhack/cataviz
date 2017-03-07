<?php
include ( dirname(__FILE__).'/Cataviz.php' );
$db = new Cataviz( "databnf.sqlite" );
if ( isset( $_REQUEST['person'] ) ) $persark=$_REQUEST['person'];
else $persark = "cb11888978p"; // Apollinaire
$person = $db->person( $persark );
// si plus de 1000
$from = @$_REQUEST['from'];
$to = @$_REQUEST['to'];



?><!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <link rel="stylesheet" type="text/css" href="cataviz.css"/>
    <style>
    </style>
  </head>
  <body>
<!-- Formulaire de dates si plus de 1000 docs ? -->

    <table class="sortable">
      <tr>
        <th>N°</th>
        <th>Date</th>
        <th>Titre</th>
        <!--
        <th>Rôle</th>
      -->
        <th>Description</th>
      </tr>
      <?php
$qdoc = $db->prepare( "SELECT * FROM document WHERE id = ?; " );
$qcont = $db->prepare( "SELECT document FROM contribution WHERE person = ? ORDER BY DATE " );
$qcont->execute( array( $person['id'] ) );
$i = 1;
while ( $cont = $qcont->fetch( PDO::FETCH_ASSOC ) ) {
  $qdoc->execute( array( $cont['document'] ) );
  $doc = $qdoc->fetch( PDO::FETCH_ASSOC );
  echo '<tr>';
  echo '<td>'.$i.'</td>';
  echo '<td>'.$doc['date'].'</td>';
  echo '<td>'.$doc['title'].'</td>';
  echo '<td>'.$doc['description'].'</td>';
  echo "</tr>\n";
  $i++;
}
      ?>
    </table>
    <script type="text/javascript" src="Sortable.js">//</script>
  </body>
</html>
