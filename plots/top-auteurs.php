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
    <table class="data">
      <caption>BnF, catalogue général, auteurs les plus publiés entre <?= Cataviz::$p['from'] ?> et <?= Cataviz::$p['to'] ?></caption>
      <thead>
        <tr>
          <th>n°</th>
          <th>Nom</th>
          <th>Prénom</th>
          <th></th>
          <th>Titres</th>
        </tr>
      </thead>
<?php
$sql = "SELECT pers.name, pers.given, pers.role, count(*) AS count FROM pers, contrib WHERE contrib.pers = pers.id AND contrib.type = 1 AND contrib.year >= ? AND contrib.year <= ? GROUP BY pers ORDER BY count DESC LIMIT 50;";
$q = Cataviz::prepare($sql);
$q->execute(array(Cataviz::$p['from'], Cataviz::$p['to']));
$n = 0;
while ($row = $q->fetch(PDO::FETCH_ASSOC)) {

  print "<tr>"
    . "<td align=\"right\">" . ++$n . "</td>"
    . "<td>" . $row['name'] . "</td>"
    . "<td>" . $row['given'] . "</td>"
    . "<td>" . $row['role'] . "</td>"
    . "<td align=\"right\">" . $row['count'] . "</td>"
    . "</tr>"
  ;
}

?>
    </table>
  </div>
</div>
<?php

}
