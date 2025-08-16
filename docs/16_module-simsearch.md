# Ähnlichkeitssuche

Die Ähnlichkeitssuche muss in den AddOn-Einstellungen aktiviert sein.

Search it baut anschließend bei jeder Suche, die einen Treffer ergab, einen
Schlagwortindex aus. Dabei wird angenommen, dass Wörter, die zu Suchergebnissen
führen, richtig geschrieben sind.

Sollte eine Suche keine Ergebnisse liefern, füllt Search it das Result-Array mit
eventuell gefundenen ähnlichen Wörtern und macht auch einen Vorschlag, wie der
neue Suchbegriff aussehen könnte.

> **Wichtig:** Wenn Sie die Ähnlichkeitssuche erst nachträglich aktivieren, 
> nachdem bereits ein Index existiert, können die phonetischen Werte für 
> bestehende Schlagwörter fehlen. In diesem Fall sollten Sie entweder den 
> kompletten Index neu aufbauen oder die Methode `regenerateKeywords()` 
> verwenden, um die Schlagwörter mit den aktuellen Ähnlichkeitssuche-
> Einstellungen zu regenerieren.

> Tipp: Um die Ähnlichkeitssuche effektiv einsetzen zu können, empfiehlt es
> sich, die Suche selbst mit richtigen Schlagwörtern zu füttern. Dadurch sind
> erste Suchwörter indexiert und die Ähnlichkeitssuche kann bei einer falschen
> Schreibweise dieser Wörter diese vorschlagen.

> Tipp: Die durchgeführte Ähnlichkeitssuche gibt bei der Ergebnis-Rückgabe
> zusätzliche Informationen zurück, bspw., ob sie überhaupt angewendet wurde,
> welche Begriffe berücksichtigt wurden u.a.

## Beispielmodul Output

```php
<?php
$request = rex_request('search', 'string', false);
$article = rex_article::getCurrent();
$sim_limit = 10; // Maximales Limit ähnlicher Suchbegriffe

if($request) { // Wenn ein Suchbegriff eingegeben wurde
    $server = rtrim(rex::getServer(), "/");

    // Suche wie initieren und ausführen
    $search_it = new search_it();
    $result = $search_it->search($request);

    if($result['count']) {
        // Hier bitte den Code für die Ausgabe der Suchtreffer einfügen
    }
    else if(!$result['count']) {
        echo '<p class="search_it-zero">Es wurden keine Suchergebnisse gefunden.</p>';
    }
    
    if(!$result['count'] && !empty($result['simwordsnewsearch'])){
        // Ähnlichkeitssuche ausgeben
    	$search_it->setLimit(0, 1); // um zu prüfen, ob für einen ähnlichen Begriff ein Ergebnis vorhanden ist, brauchst es nur einen Treffer
		$simwords_out = '<p>Folgende ähnliche Suchbegriffe mit Treffern wurden gefunden:<strong><ul>';
		$sim_counter = 0;
		foreach (explode(' ', trim($result['simwordsnewsearch'])) as $new_search_word) {
			$result_simwords = $search_it->search(trim($new_search_word));
			if($result_simwords['count'] > 0) {
				$simwords_out .= '<li><a href="'. $article->getUrl(['search' => $new_search_word]) .'">'. $new_search_word .'</a></li>';
				$sim_counter++;
				// Optional: Anzahl ähnlicher Suchbegriffe begrenzen
				if($sim_counter >= $sim_limit) {
					break;
				}
			}
		}
		$simwords_out .= '</ul></strong></p>';
		// Ähnlichkeitssuche nur ausgeben, wenn auch Suchtreffer für die ähnlichen Begriffe vorliegen
		if($sim_counter > 0) {
			echo $simwords_out;
		}
    }
}
```

## Schlagwörter neu generieren

Falls Sie die Ähnlichkeitssuche nachträglich aktiviert haben, können Sie die 
Schlagwörter mit den aktuellen Einstellungen neu generieren:

```php
$search_it = new search_it();
$search_it->regenerateKeywords();
```

Diese Methode liest alle existierenden Schlagwörter aus dem Index, löscht die 
Schlagwort-Tabelle und erstellt sie mit den aktuellen Ähnlichkeitssuche-
Einstellungen neu.
