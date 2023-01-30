<?php
use Oeuvres\Kit\{Select};
require_once(__DIR__ . "/../Cataviz.php");


function title()
{
    return "BnF, Catalogue général, auteurs les plus publiés par année";
}

function main() {
  ?>
<div class="form_chart">
  <form name="form">
          De <input name="from" size="4" value="<?= Cataviz::$p['from'] ?>" />
          à <input name="to" size="4" value="<?= Cataviz::$p['to'] ?>" />
          <button type="submit">▶</button>
  </form>
  <div id="row">

    <nav class="top-table">
      <header>
        <div class="caption">BnF, catalogue général, auteurs les plus publiés entre <?= Cataviz::$p['from'] ?> et <?= Cataviz::$p['to'] ?></div>
        <div class="top-row">
          <span class="num">n°</span>
          <span class="name">Auteur</span>
          <span class="num">Titres</span>
        </div>
      </header>
  <?php
  $sql = "SELECT pers, count(*) AS count FROM contrib WHERE type = 1 AND year >= ? AND year <= ? GROUP BY pers ORDER BY count DESC LIMIT 50;";
  $contrib_q = Cataviz::prepare($sql);
  $sql = "SELECT * FROM pers WHERE id = ? ";
  $pers_q = Cataviz::prepare($sql);

  $contrib_q->execute(array(Cataviz::$p['from'], Cataviz::$p['to']));
  $n = 0;
  $pers_first = null;
  while ($contrib_row = $contrib_q->fetch(PDO::FETCH_ASSOC)) {
    $pers_q->execute([$contrib_row['pers']]);
    $pers_row = $pers_q->fetch(PDO::FETCH_ASSOC);
    // get first pers to show its record
    if (!$pers_first) $pers_first = $contrib_row['pers'];
    print '<a class="top-row" href="data/pers_bibl.php?pers=' . $contrib_row['pers'] . '" target="author">';
    print '<span class="num">' . ++$n . '.</span>';
    print '<span class="name">';
    print $pers_row['name'];
    if ($pers_row['given']) print ", " . $pers_row['given'];
    if ($pers_row['role']) print ", " . $pers_row['role'];
    if ($pers_row['birthyear'] || $pers_row['deathyear']) {
      print " (";
      if ($pers_row['birthyear']) print $pers_row['birthyear'];
      else print " ";
      print "–";
      if ($pers_row['deathyear']) print $pers_row['deathyear'];
      else print " ";
      print ")";
    }
    print '</span>';
    print '<span class="num">' . $contrib_row['count'] . '</span>';
    print '</a>';
  }

  ?>
    </nav>
    <iframe frameBorder="0" width="68%" name="author" src="data/pers_bibl.php?pers=<?= $pers_first ?>"/>
  </div>
</div>
<?php

}
