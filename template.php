<?php
declare(strict_types=1);

require_once(__DIR__ . "/Cataviz.php");
$db = new Cataviz("cataviz.db");


use Oeuvres\Kit\{I18n, Route, Web};

$body_class = 'plot';

function menu_item($url, $label, $title=null, $pars=['from', 'to'])
{
    $html = '<a href="';
    $html .= Route::home_href();
    $html .= $url;
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
            $html .= $key . '=' . Cataviz::$p[$key];
        }
    }
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
        <script src="https://cdnjs.cloudflare.com/ajax/libs/dygraph/2.1.0/dygraph.min.js">//</script>
        <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/dygraph/2.1.0/dygraph.min.css"/>
        <script src="<?= Route::home_href() ?>theme/dygraphPlotHistory.js">//</script>
        <script src="<?= Route::home_href() ?>theme/formajax.js">//</script>
        <script type="text/javascript" src="<?= Route::home_href() ?>theme/cataviz.js">//</script>
        <link rel="stylesheet" type="text/css" href="<?= Route::home_href() ?>theme/cataviz.css"/>
    </head>
    <body class="<?= $body_class ?>">
        <div id="page">
            <header id="header">
                <nav class="menu" id="top">
<a style="float:left" href="." class="plus">◀ Cataviz</a>
<?= 
menu_item('titres', 'Titres', 'Chronologie générale des publications', ['from', 'to']);
menu_item('demographie', 'Démographie', 'Mortalité, “Natalité”, générations…', ['from', 'to']);

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