# Ähnlichkeitssuche

Die Ähnlichkeitssuche muss in den AddOn-Einstellungen aktiviert sein.

Search it baut anschließend bei jeder Suche, die einen Treffer ergab, einen Schlagwortindex aus. Dabei wird angenommen, dass Wörter, die zu Suchergebnissen führen, richtig geschrieben sind.

Sollte eine Suche keine Ergebnisse liefern, füllt Search it das Result-Array mit eventuell gefundenen ähnlichen Wörtern und macht auch einen Vorschlag, wie der neue Suchbegriff aussehen könnte.

> Tipp: Um die Ähnlichkeitssuche effektiv einsetzen zu können, empfiehlt es sich, die Suche selbst mit richtigen Schlagwörtern zu füttern. Dadurch sind erste Suchwörter indexiert und die Ähnlichkeitssuche kann bei einer falschen Schreibweise dieser Wörter diese vorschlagen.

> Tipp: Die durchgeführte Ähnlichkeitssuche gibt bei der Ergebnis-Rückgabe zusätzliche Informationen zurück, bspw., ob sie überhaupt angewendet wurde, welche Begriffe berücksichtigt wurden u.a.

## Beispielmodul Output

```
<?php
$article_id = rex_article::getCurrentId();
$article = rex_article::get($article_id);
$request = rex_request('search', 'string', false);

if($request) { // Wenn ein Suchbegriff eingegeben wurde
	$server = rtrim(rex::getServer(), "/");
	
	print '<section class="search_it-hits">';
	
	// Suche initialisieren (nur Artikel in der aktuellen Sprache)
    $search_it = new search_it(rex_clang::getCurrentId());
	// Suche ausführen
    $result = $search_it->search($request);

	echo '<h2 class="search_it-headline">Suchergebnisse</h2>';
	if($result['count']) {
 		// Suchergebnisse ausgeben
    }
	else if(!$result['count']) {
		echo '<p class="search_it-zero">Es wurden keine Suchergebnisse gefunden.</p>';

		$activate_similarity_search = "REX_VALUE[2]" == 'true' ? TRUE : FALSE;
		// Ähnlichkeitssuche
		if($activate_similarity_search && rex_config::get('search_it', 'similarwordsmode', 0) > 0 && count($result['simwords']) > 0){
			$newsearchString = $result['simwordsnewsearch'];
			$result_simwords = $search_it->search($newsearchString);
			if($result_simwords['count'] > 0){
				echo '<p>Ähnliche Suche mit Treffern: "<strong><a href="'. $article->getUrl(['search' => $newsearchString]) .'">'. $newsearchString .'</a></strong>"</p>';
			}
		}
	}
	print "</section>";
}
?>
```