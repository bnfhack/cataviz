<?php

function title()
{
    return "BnF, Catalogue général, titres en français par an";
}

function main()
{
    /*
      <div class="links">
        <a href="?">Data.bnf.fr, titres en français par an, moyenne du nombre de pages</a> :
        <a href="?from=1600&amp;to=1788&amp;smooth=4">1600–1789</a>,
        <a href="?from=1780&amp;to=1860&amp;smooth=1">1789–1870</a>,
        <a href="?from=1860&amp;to=1958&amp;smooth=1">1870–1960</a>,
        <a href="?from=1950&amp;to=<?=$datemax?>&amp;smooth=1">1950–…</a>
      </div>

        <label>Seuil pages <input name="pagefloor" size="4" value="<?php echo  $pagefloor ?>"/></label>
        <label>Lissage <input name="smooth" size="1" value="<?php echo  $smooth ?>"/></label>
        Échelle
        <button id="log" <?php if($log) echo'disabled="true"';?> type="button">log</button>
        <button id="linear" <?php if(!$log) echo'disabled="true"';?> type="button">linéaire</button>

      */
?>
<div class="form_chart">
    <form name="dates">
        <button onclick="window.location.href='?'; " type="button">Reset</button>
        De <input name="from" size="4" value="<?= Cataviz::$p['from'] ?>" />
        à <input name="to" size="4" value="<?= Cataviz::$p['to'] ?>" />
        <button type="submit">▶</button>
    </form>
    <div id="chart" class="dygraph"></div>
</div>
<script type="text/javascript">
let data = [<?php

$qdoc = Cataviz::prepare("SELECT count(*) AS count FROM doc WHERE year = ? AND type = 'txt'");
$qgdp = Cataviz::prepare("SELECT gdppc FROM gdppc WHERE year = ?");
// TODO pages
// $qpages = $db->prepare("SELECT avg(pages) FROM document WHERE date = ?  AND type = 'Text' AND lang='fre'");


$lastpages = 0;
for ($date = Cataviz::$p['from']; $date <= Cataviz::$p['to']; $date++) {
    $qdoc->execute(array($date));
    list($doc) = $qdoc->fetch(PDO::FETCH_NUM);
    $qgdp->execute(array($date));
    list($gdp) = $qgdp->fetch(PDO::FETCH_NUM);
    echo "\n[" . $date;
    echo ", " . $doc;
    echo ", " . $gdp;
    echo "],";
}
// nom de colonnes
// $A = "Titres > $pagefloor p.";
// $B = "Titres <= $pagefloor p.";
?>
]; // data
let attrs = {
    title: "<?= title() ?>",
    labels: [ "Année", "Titres", "PNB/h"],
    legend: "always",
    ylabel: "Titres",
    y2label: "PNB/h",
    stackedGraph: true,
    showRoller: true,
    logscale: true,
    // valueRange: [500, null],
    series: {
        "Titres ? p.": {
            color: "rgba(1, 1, 1, 1)",
            strokeWidth: 1,
            fillGraph: false,
        },
        "PNB/h": {
            axis: 'y2',
            color: "rgba(255, 128, 128, 1)",
            strokeWidth: 2,
            strokePattern: [4,4],
            stackedGraph: false,
            fillGraph: false,
        },
    },
};
var annoteSeries = "Titres"; // period anotations
</script>
<div class="text">
    <p>
        Le nombre de titres français par an qui entrent au catalogue augmente beaucoup au cours des siècles,
        de quelques titres au début de la bibliothèque (1537), à plusieurs dizaines de milliers de nos jours.
        Les effectifs sont à observer en tendance, car ils sont affectés par différents taux d’erreur qui se combinent.
        7% des notices sont sans date, 11% n’ont pas de langue, le taux de documents sans nombre de pages (repérable automatiquement)
        est variable.
        La variation du nombre moyen de pages reste cependant très significative des événements historiques (guerres, révolutions…),
        affectant une édition très concentrée à <a href="paris.php">Paris</a>.
        On remarque par exemple que l’agitation de la Fronde, de 1789 ou de 1848 produit beaucoup de titres de peu de pages (brochures)
        mais affecte beaucoup moins les nombre de titres de plus de 100 pages.
        Les guerres, par contre, affectent durement tous les genres éditoriaux (restrictions de papier, blocage de Paris).
        https://www.rug.nl/ggdc/historicaldevelopment/maddison/?lang=en
    </p>
</div>
<?php

}
