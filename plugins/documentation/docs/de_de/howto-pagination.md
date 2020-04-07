# Pagination

## Suche mit Pagination

Für umfangreiche Webauftritte kann eine Pagination für die Suchergebnisse sinnvoll oder notwendig sein.

```
<?php
$article_id = rex_article::getCurrentId();
$article = rex_article::get($article_id);
$request = rex_request('search', 'string', false);
$limit = "REX_VALUE[1]" ?: 10;
$start = rex_request('start', 'int', 0);

if($request) { // Wenn ein Suchbegriff eingegeben wurde
	$server = rtrim(rex::getServer(), "/");
	
	print '<section class="search_it-hits">';
	
	// Suche initialisieren (nur Artikel in der aktuellen Sprache)
    $search_it = new search_it(rex_clang::getCurrentId());
	// Limit für Pagination setzen
	$search_it->setLimit($start, $limit);
	// Suche ausführen
    $result = $search_it->search($request);

	echo '<h2 class="search_it-headline">Suchergebnisse</h2>';
	if($result['count']) {
 		// Pagination
		$pagination = "";
		if($result['count'] > $limit) {
			$pagination = '<ul class="pagination">';
			for($i = 0; ($i * $limit) < $result['count']; $i++){
				if(($i * $limit) == $start){
					$pagination .= '<li class="current">'. ($i + 1) .'</li>';
				}
				else {
					$pagination .= '<li><a href="'. $article->getUrl(['search' => $request, 'start' => $i * $limit]) .'">'. ($i + 1) .'</a></li>';
				}
			}
			$pagination .= '</ul><br>';
		}
		// Pagination oberhalb der Suchergebnisse ausgeben
		echo $pagination;
		
		// Suchergebnisse ausgeben
		echo '<ul class="search_it-results">';                           
        foreach($result['hits'] as $hit) {
			// Suchergebnisse ausgeben
        }
        echo '</ul><br>';

		// Pagination unterhalb der Ergebnisse ausgeben
		echo $pagination;	
    }
	else if(!$result['count']) {
		echo '<p class="search_it-zero">Es wurden keine Suchergebnisse gefunden.</p>';
	}
	print "</section>";
}
?>
```