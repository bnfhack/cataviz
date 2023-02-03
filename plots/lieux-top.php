<?php
require_once(__DIR__ . "/../Cataviz.php");

use Oeuvres\Kit\{Http};


function title()
{
    return "BnF, Catalogue général, lieux de publication les plus fréquents";
}

function main() {

  ?>
<div class="form_chart">
  <form name="form">
          De <input name="from" size="4" value="<?= Cataviz::$p['from'] ?>" />
          à <input name="to" size="4" value="<?= Cataviz::$p['to'] ?>" />.
          <button type="submit">▶</button>
  </form>
  <div id="row">

    <nav class="top-table">
      <header>
        <div class="caption">BnF, catalogue général, lieux de publication les plus fréquents entre <?= Cataviz::$p['from'] ?> et <?= Cataviz::$p['to'] ?></div>
        <div class="top-row">
          <span class="num">n°</span>
          <span class="name">Lieu</span>
          <span class="num">Titres</span>
        </div>
      </header>
  <?php

  $sql = "SELECT place, count(*) AS count FROM doc WHERE year >= ? AND year <= ?  GROUP BY place ORDER BY count DESC;";
  
  $place_q = Cataviz::prepare($sql);
  $place_q->execute(array(Cataviz::$p['from'], Cataviz::$p['to']));
  
  $n = 0;
  $n_max = 500;
  $place_first = null;
  while ($place_row = $place_q->fetch(PDO::FETCH_ASSOC)) {
    // get first pers to show its record
    if (!$place_first) $place_first = $place_row['place'];
    print '<div class="top-row">';
    //  href="data/pers_bibl.php?pers=' . $contrib_row['pers'] . '" target="author">';
    print '<span class="num">' . ++$n . '.</span> ';
    print '<span class="name">' . $place_row['place'] . '</span> ';
    print '<span class="num">' . $place_row['count'] . '</span>';
    print '</div>';
    flush();
    if ($n >= $n_max) break;
  }

  ?>
    </nav>
    <!--
    <iframe frameBorder="0" width="68%" name="author" src="data/pers_bibl.php?pers=<?= $pers_first ?>"/>
-->
  </div>
</div>
<?php

}
