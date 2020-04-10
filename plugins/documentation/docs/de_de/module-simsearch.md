# Ähnlichkeitssuche

Die Ähnlichkeitssuche muss in den AddOn-Einstellungen aktiviert sein.

Search it baut anschließend bei jeder Suche, die einen Treffer ergab, einen Schlagwortindex aus. Dabei wird angenommen, dass Wörter, die zu Suchergebnissen führen, richtig geschrieben sind.

Sollte eine Suche keine Ergebnisse liefern, füllt Search it das Result-Array mit eventuell gefundenen ähnlichen Wörtern und macht auch einen Vorschlag, wie der neue Suchbegriff aussehen könnte.

> Tipp: Um die Ähnlichkeitssuche effektiv einsetzen zu können, empfiehlt es sich, die Suche selbst mit richtigen Schlagwörtern zu füttern. Dadurch sind erste Suchwörter indexiert und die Ähnlichkeitssuche kann bei einer falschen Schreibweise dieser Wörter diese vorschlagen.

> Tipp: Die durchgeführte Ähnlichkeitssuche gibt bei der Ergebnis-Rückgabe zusätzliche Informationen zurück, bspw., ob sie überhaupt angewendet wurde, welche Begriffe berücksichtigt wurden u.a.

## Beispielmodul Output

```php
<?php
$request = rex_request('search', 'string', false);

if($request) { // Wenn ein Suchbegriff eingegeben wurde
    $server = rtrim(rex::getServer(), "/");

    print '<section class="search_it-hits">';

    // Init search and execute
    $search_it = new search_it();
    $result = $search_it->search($request);

    echo '<h2 class="search_it-headline">Suchergebnisse</h2>';
    if($result['count'] == 0 && count($result['simwords']) > 0){
        // Ähnlichkeitssuche ausgeben
        $newsearchString = $result['simwordsnewsearch'];
        $result_simwords = $search_it->search($newsearchString);
        if($result_simwords['count'] > 0){
            echo '<p>Meinten Sie <strong>'. $newsearchString .'</strong>?</p>';
        }
    }

    if($result['count']) {
        // Ausgabe der Treffer
    }
    else if(!$result['count']) {
        echo '<p class="search_it-zero">{{ d2u_helper_module_14_search_results_none }}</p>';
    }
    print "</section>";
}
```
