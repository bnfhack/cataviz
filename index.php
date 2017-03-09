<?php
include ( dirname(__FILE__).'/Cataviz.php' );
$db = new Cataviz( "databnf.sqlite" );
$from = @$_REQUEST['from'];
$to = @$_REQUEST['to'];
$pre = @$_REQUEST['pre'];
$pstart = @$_REQUEST['pstart'];
$pend = @$_REQUEST['pend'];
$dead = @$_REQUEST['dead'];
$orderpers = 'posthum';
if ( isset($_REQUEST['orderpers']) ) $orderpers = $_REQUEST['orderpers'];
if ( !preg_match( '/posthum|anthum|docs/', $orderpers ) ) $orderpers = 'posthum';
if ( isset($_REQUEST['limit']) ) $limit = 0+$_REQUEST['limit'];
if ( !isset($limit) || $limit < 1 || $limit > 500 ) $limit = 50;

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
    <h1><a href="?">Cataviz</a></h1>
    <p>Cataviz permet d’explorer les données data.bnf.fr, non pour trouver des livres, ce que le site web de la BNF fait mieux, mais pour en extraire des statistiques et des graphiques. C’est une maison ouverte pour tester des vues, des hypothèses…
    Le code PHP de cette application est sur <a href="http://github.com/bnfhack/cataviz">Github</a>.
    Les données sont librement (!!!) téléchargeables sur <a href="http://data.bnf.fr/semanticweb">data.bnf.fr</a>,
    elles sont propulsées par une base SQLite produite avec <a href="http://github.com/bnfhack/databnf2sql">databnf2sql</a>.
  </p>
    <?php include ( dirname(__FILE__).'/footer.php' ) ?>
    <script type="text/javascript" src="Sortable.js">//</script>
  </body>
</html>
<?php

?>
