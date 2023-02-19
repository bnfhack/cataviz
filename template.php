<?php
declare(strict_types=1);

require_once(__DIR__ . "/Cataviz.php");
$db = new Cataviz("cataviz.db");


use Oeuvres\Kit\{Http, I18n, Route, Web};

$body_class = 'plot';

function menu_item($url, $label, $title=null)
{
    $html = '<a href="';
    $html .= Route::home_href();
    $html .= $url;
    /* done by js
    if ($pars && count($pars) > 0) {
        $first = true;
        foreach($pars as $key) {
            if ($first) {
                $first = false;
                $html .= '?';
            }
            else {
                $html .= '&amp;';
            }
            $html .= $key . '=' . Http::par($key);
        }
    }
    */
    $html .= '"';
    if ($title) $html .= ' title="' . $title . '"';
    $html .= '>' . $label . '</a>';
    return $html;
}

?><!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8"/>
        <title><?= Route::title(I18n::_('title')) ?></title>
        <script src="<?= Route::home_href() ?>theme/dygraph.min.js">//</script>
        <link rel="stylesheet" type="text/css" href="<?= Route::home_href() ?>theme/dygraph.css"/>
        <script src="<?= Route::home_href() ?>theme/dygraphPlotHistory.js">//</script>
        <script src="<?= Route::home_href() ?>theme/formajax.js">//</script>
        <link rel="stylesheet" type="text/css" href="<?= Route::home_href() ?>theme/cataviz.css"/>
    </head>
    <body class="<?= $body_class ?>">
        <div id="page">
            <header id="header">
                <nav class="menu" id="menu">
<a href="." class="plus">◀ Cataviz</a>
<?php
// echo menu_item('titres', 'Titres', 'Chronologie générale des publications');
// echo menu_item('demographie', 'Démographie', 'Mortalité, “Natalité”, générations…', ['from', 'to']);
echo menu_item('formats', 'Formats', 'Répartition par formats in-8°, in-4°…');
echo menu_item('langues', 'Langues', 'Répartition par langues');
echo menu_item('pages', 'Pages', 'Répartition par tailles en pages');
echo menu_item('premiers', '1er livres', 'Répartition entre premiers livres et suivants');
echo menu_item('auteurs', 'Auteurs', 'Auteurs, rythme chronologique de publication');
echo menu_item('sujets', 'Personnes sujet', 'Personnes sujet d’un titre');
echo menu_item('lieux', 'Lieux d’édition', 'Lieux d’édition');
echo menu_item('clement', 'Classement Clément', 'Plan de classement selon la cote Clément (1647 / 1712)');
echo menu_item('parite', 'Parité', 'Parité entre auteurs femmes et hommes');

?>

                </nav>
            </header>
            <div id="content">
                <?php Route::main(); ?>
            </div>
            <footer id="footer"><a href="#top" style="float: right; ">▲</a>Données BnF, <a href="https://api.bnf.fr/fr/BnF-Catalogue-general" target="_blank">catalogue général</a> (2020), développements <a onmouseover="this.href='mailto'+'\x3A'+'frederic.glorieux'+'\x40'+'fictif.org'" href="#">Frédéric Glorieux</a>. </footer>
        </div>
        <script type="text/javascript" src="<?= Route::home_href() ?>theme/Sortable.js">//</script>
        <script type="text/javascript" src="<?= Route::home_href() ?>forms.js">//</script>
    </body>
</html>