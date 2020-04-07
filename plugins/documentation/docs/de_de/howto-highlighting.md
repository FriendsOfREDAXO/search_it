# Suchergebnisse im Ziel-Artikel hervorheben

Search it kann nicht nur Suchergebnisse innerhalb der Suchergebnis-Liste hervorheben, sondern auch in den betroffnen Artikeln. Dazu muss der `Search Highlighter` in den Search it-Einstellungen aktiviert sein.

Damit die Suche den Suchbegriff an die aufgerufene Seite übergibt, muss der Link in der Suchergebnis-Liste angepasst werden.

##Beispielmodul Ausgabe

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
		echo '<ul class="search_it-results">';                           
        foreach($result['hits'] as $hit) {
            if($hit['type'] == 'article') {
				// Artikel
                $article_hit = rex_article::get($hit['fid']);
				// Falls YCom Addon genutzt wird: zuerst Benuterrechte prüfen
				if(rex_addon::get('ycom')->isAvailable() == false || (rex_addon::get('ycom')->isAvailable() && rex_ycom_auth::articleIsPermitted($article_hit))) {
					// Falls YRewrite genutzt wird die korrekte Domain holen
					$hit_server = $server;
					if(rex_addon::get('yrewrite')->isAvailable()) {
						$hit_domain = rex_yrewrite::getDomainByArticleId($hit['fid'], $hit['clang']);
						$hit_server = rtrim($hit_domain->getUrl(), "/");
					}

					echo '<li class="search_it-result search_it-article">';
					// Artikellink MIT highlighter auf der Trefferseite ausgeben
					echo '<span class="search_it-title"><a href="'. $hit_server . $article_hit->getUrl(['search_highlighter' => $request]) .'" title="'. $article_hit->getName() .'">'. $article_hit->getName() .'</a></span><br>';
					// Artikellink OHNE highlighter auf der Trefferseite ausgeben
//					echo '<span class="search_it-title"><a href="'. $hit_server . $article_hit->getUrl() .'" title="'. $article_hit->getName() .'">'. $article_hit->getName() .'</a></span><br>';
					echo '<span class="search_it-teaser">'. $hit['highlightedtext'] .'</span><br>';
					// Artikellink MIT highlighter auf der Trefferseite ausgeben
					echo '<span class="search_it-url"><a href="'. $hit_server . $article_hit->getUrl(['search_highlighter' => $request]) .'" title="'. $article_hit->getName() .'">'. urldecode($hit_server . $article_hit->getUrl()) .'</a></span>';
					// Artikellink OHNE highlighter auf der Trefferseite ausgeben
//					echo '<span class="search_it-url"><a href="'. $hit_server . $article_hit->getUrl(['search_highlighter' => $request]) .'" title="'. $article_hit->getName() .'">'. urldecode($hit_server . $article_hit->getUrl()) .'</a></span>';
					echo '</li>';
				}
            }
    }
	else if(!$result['count']) {
		echo '<p class="search_it-zero">Es wurden keine Suchergebnisse gefunden.</p>';
	}
	print "</section>";
}
?>
```

Der relevante Teil ist folgender:

```
					// Artikellink MIT highlighter auf der Trefferseite ausgeben
					echo '<span class="search_it-title"><a href="'. $hit_server . $article_hit->getUrl(['search_highlighter' => $request]) .'" title="'. $article_hit->getName() .'">'. $article_hit->getName() .'</a></span><br>';

					// Artikellink OHNE highlighter auf der Trefferseite ausgeben
					echo '<span class="search_it-title"><a href="'. $hit_server . $article_hit->getUrl() .'" title="'. $article_hit->getName() .'">'. $article_hit->getName() .'</a></span><br>';
```

Dadruch wird der Parameter search_highlighter an die Seite übergeben und kann dort ausgelesen werden.