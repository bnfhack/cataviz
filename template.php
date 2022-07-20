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
        <script src="lib/dygraph.min.js">//</script>
        <link rel="stylesheet" type="text/css" href="lib/dygraph.css"/>
        <link rel="stylesheet" type="text/css" href="cataviz.css"/>
    </head>
    <body class="<?= $body_class ?>">
        <div id="page">
            <header id="header">
                <nav class="menu" id="top">
<a style="float:left" href="." class="plus">◀ Cataviz</a>
<?= menu_item('chrono', 'Titres', 'Chronologie générale des publications', ['from', 'to']) ?>

                </nav>
            </header>
            <div id="content">
                <div class="content">
                    <?php Route::main(); ?>
                </div>
            </div>
            <footer id="footer"><a href="#top" style="float: right; ">▲</a>Données BnF, <a href="https://api.bnf.fr/fr/BnF-Catalogue-general" target="_blank">catalogue général</a> (2020), développements <a onmouseover="this.href='mailto'+'\x3A'+'frederic.glorieux'+'\x40'+'fictif.org'" href="#">Frédéric Glorieux</a>. </footer>
        </div>
        <script type="text/javascript" 
        src="<?= Route::home_href() ?>theme/cataviz.js">//</script>
        <script type="text/javascript" src="<?= Route::home_href() ?>lib/Sortable.js">//</script>
        <script type="text/javascript" src="<?= Route::home_href() ?>forms.js">//</script>
    </body>
</html>