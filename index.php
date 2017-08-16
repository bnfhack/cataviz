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
    <link rel="stylesheet" type="text/css" href="cataviz.css"/>
    <style>
    </style>
  </head>
  <body>
    <?php include ( dirname(__FILE__).'/menu.php' ) ?>
    <div class="text">
      <h1><a href="?">Cataviz</a></h1>
      <p>Cataviz permet d’explorer les données data.bnf.fr, non pour trouver des livres, ce que le site web de la BNF fait mieux, mais pour en extraire des statistiques et des graphiques. C’est une maison ouverte pour tester des vues, des hypothèses…
      Le code PHP de cette application est sur <a href="http://github.com/bnfhack/cataviz">Github</a>.
      Les données sont librement (!!!) téléchargeables sur <a href="http://data.bnf.fr/semanticweb">data.bnf.fr</a>,
      elles sont propulsées par une base SQLite produite avec <a href="http://github.com/bnfhack/databnf2sql">databnf2sql</a>.
      Quelques explication plus rédigées :
      </p>
      <ul>
        <li><a href="https://resultats.hypotheses.org/1048">Femmes de lettres, démographie (data.bnf.fr 2017)</a></li>
        <li><a href="https://resultats.hypotheses.org/795">Data.bnf.fr, les documents</a></li>
      </ul>
      <p>Vous pouvez commencer à explorer le catalogue par <a href="auteur.php?persark=cb11928669t">Voltaire</a>, <a href="relations.php?persark=cb11888978p">Apollinaire</a>, les <a href="biblio.php?title=vampir*">vampires</a>, ou la <a href="femmes.php">place des femmes auteur</a>.
      </p>
    </div>
    <?php include ( dirname(__FILE__).'/footer.php' ) ?>
    <script type="text/javascript" src="Sortable.js">//</script>
  </body>
</html>
<?php

?>
