<?php
use Oeuvres\Kit\{Http, Route, Select};

function title()
{
    return "BnF, Catalogue général, auteur, bilbiographie";
}

function main()
{

    $start = Http::int('start', 1685, 1452, 2019);
    $end = Http::int('end', 1913, 1452, 2019);
?>
<div class="form_chart">
    <form name="form" class="line">

        <label>De <input class="year" name="start" size="4" value="<?= $start ?>" /></label>
        <label>à <input class="year" name="end" size="4" value="<?= $end ?>" /></label>
        <button id="submit" type="submit">▶</button>
    </form>
    <div id="row">
        <div id="auteurs" data-url="data/suggest_contrib.php" data-name="pers">
            <form>
                <input name="q" class="q" placeholder="Filtrer par lettres"/>
                <div title="Date de naissance ou du premier livre selon l’information disponible">“Génération”
                après <input class="year" name="after" size="4"/>
                avant <input class="year" name="before" size="4"/>
                </div>
            </form>
            <nav>

            </nav>
        </div>
    </div>
</div>
<script type="text/javascript" src="<?= Route::home_href() ?>theme/cataviz.js">//</script>
<script type="text/javascript">

Cataviz.suggInit('sugg');
Cataviz.suggUp();
<?= Cataviz::url_pers() ?>
Cataviz.chartUp();


</script>
<?php

}
