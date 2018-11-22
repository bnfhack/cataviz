<?php
$_l="cb11888978p";
if (isset($persark) && $persark) $_l=$persark;
?><nav class="menu" id="top">
  <a style="float:left" href="." class="plus">◀ Cataviz</a>
  <dl class="plus">
    <dt>Les documents</dt>
    <dd>
      <a href="chrono.php?from=<?=$from?>&amp;to=<?=$to?>&amp;log=<?=$log?>" title="Chronologie générale des publications">Titres</a>
      <a href="paris.php?from=<?=$from?>&amp;to=<?=$to?>&amp;log=<?=$log?>" title="Importance de Paris dans les publications">Lieu d’édition</a>
      <a href="femmes.php?from=<?=$from?>&amp;to=<?=$to?>&amp;log=<?=$log?>" title="Proportion des titres écrits par des femmes">Livres de femmes</a>
      <a href="siecles.php?from=<?=$from?>&amp;to=<?=$to?>&amp;log=<?=$log?>" title="Rééditions selon le siècle de naissance de l’auteur">Siècles</a>
      <a href="antiq.php?from=<?=$from?>&amp;to=<?=$to?>" title="Langues anciennes et traductions classiques">Antiquité</a>
      <a href="zipf.php" title="Nombre de titres par auteur">Zipf</a>
    </dd>
  </dl>
  <dl class="plus">
    <dt>Les auteurs</dt>
    <dd>
      <a href="morts.php?from=<?=$from?>&amp;to=<?=$to?>" title="Mortalité et longévité des auteurs, ratio sexuel">Mortalité</a>
      <a href="lieumort.php?from=<?=$from?>&amp;to=<?=$to?>" title="Âge et lieu de mort (Paris/Ailleurs), à la date de mort.">Lieux de mort</a>
      <a href="natalite.php?from=<?=$from?>&amp;to=<?=$to?>&amp;log=<?=$log?>" title="Natalité des auteurs à leur date de naissance, ratio sexuel">Natalité</a>
      <a href="lieunaissance.php?from=<?=$from?>&amp;to=<?=$to?>" title="Lieux de naissance (Paris/Ailleurs), à la date du premier livre.">Lieux de naissance</a>
      <a href="ages.php?from=<?=$from?>&amp;to=<?=$to?>" title="Répartition des auteurs à la date de publication">Âges</a>
      <a href="lieunaissance.php?from=<?=$from?>&amp;to=<?=$to?>" title="Lieux de naissance (Paris/Ailleurs), à la date du premier livre.">Lieux de naissance</a>
      <a href="premier.php?from=<?=$from?>&amp;to=<?=$to?>&amp;log=<?=$log?>" title="Âge moyen à la publication et au premeir livre">Âge à la publication</a>
      <a href="generations.php?from=<?=$from?>&amp;to=<?=$to?>" title="Génération des auteurs à la date de publication">Générations</a>
      <a href="population.php?from=<?=$from?>&amp;to=<?=$to?>&amp;log=<?=$log?>" title="Population des auteurs vivants, ratio sexuel">Population</a>
    </dd>
  </dl>
  <dl class="plus">
    <dt>Une œuvre</dt>
    <dd>
      <a href="palmares.php" title="Palmarès d’auteurs">Palmarès</a>
      <a href="auteur.php?persark=<?php echo $_l; ?>" title="Chronologie bibliographique pour un auteur">Auteur</a>
      <a href="relations.php?persark=<?php echo $_l; ?>" title="Relations d’un auteur avec les personnes collaborant à un document">Réseau</a>
      <a href="biblio.php?persark=<?php echo $_l; ?>" title="Recherche bibliographique par auteur, dates, mots du titre">Bibliographie</a>
    </dd>
  </dl>
</nav>
