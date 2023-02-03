<?php
require_once(__DIR__ . "/../Cataviz.php");

use Oeuvres\Kit\{Http};


function title()
{
    return "BnF, Catalogue général, auteurs les plus publiés par année";
}

function main() {
  $after = Http::par('after', '');
  $before = Http::par('before', '');
  ?>
<div class="form_chart">
  <form name="form">
          De <input name="from" size="4" value="<?= Cataviz::$p['from'] ?>" />
          à <input name="to" size="4" value="<?= Cataviz::$p['to'] ?>" />.

          <label title="Date du premier livre ou date de naissance lorsque connue et avant 1500">Génération :</label>
          après <input name="after" size="4" value="<?= $after ?>" />,
          avant <input name="before" size="4" value="<?= $before ?>" />.

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
  $sql = "SELECT pers, count(*) AS count FROM contrib WHERE type = 1 AND year >= ? AND year <= ? GROUP BY pers ORDER BY count DESC;";
  $contrib_q = Cataviz::prepare($sql);
  $sql = "SELECT * FROM pers WHERE id = ? ";
  $pers_q = Cataviz::prepare($sql);

  $contrib_q->execute(array(Cataviz::$p['from'], Cataviz::$p['to']));
  $n_max = 100;
  $n = 0;
  $pers_first = null;
  while ($contrib_row = $contrib_q->fetch(PDO::FETCH_ASSOC)) {
    $pers_q->execute([$contrib_row['pers']]);
    $pers_row = $pers_q->fetch(PDO::FETCH_ASSOC);
    if ($after) {
      if ($pers_row['doc1'] === null) continue;
      if ($pers_row['doc1'] < $after) continue;
      if ($pers_row['birthyear'] && $pers_row['birthyear'] < $after) continue;
      if ($pers_row['deathyear'] && ($pers_row['deathyear'] - 50) < $after) continue;
    }
    if ($before) {
      // <1500, birth year, too young born
      if ($pers_row['birthyear'] && $pers_row['birthyear'] < 1500) {
        if ($pers_row['birthyear'] > $before) continue;
      }
      // <1500, death year, too young dead
      else if ($pers_row['deathyear'] && $pers_row['deathyear'] - 50 < 1500) {
        if($pers_row['deathyear'] - 50 > $before) continue;
      }
      // no doc1 ?
      else if ($pers_row['doc1'] === null) {
        continue;
      }
      // test doc1
      else {
        if ($pers_row['doc1'] > $before) continue;
      }
    }
    // get first pers to show its record
    if (!$pers_first) $pers_first = $contrib_row['pers'];
    print '<a class="top-row" href="data/pers_bibl.php?pers=' . $contrib_row['pers'] . '" target="author">';
    print '<span class="num">' . ++$n . '.</span>';
    print '<span class="name">';
    print Cataviz::pers_label($pers_row);
    print '</span>';
    print '<span class="num">' . $contrib_row['count'] . '</span>';
    print '</a>';
    flush();
    if ($n >= $n_max) break;
  }

  ?>
    </nav>
    <iframe frameBorder="0" width="68%" name="author" src="data/pers_bibl.php?pers=<?= $pers_first ?>"/>
  </div>
</div>
<?php

}
