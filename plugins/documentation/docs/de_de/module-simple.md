# Einfache Artikel-Suchergebnisse

Dieses Suchergebnis-Modul nimmt einen Suchbegriff mittels GET/POST-Parameter `search` entgegen und gibt gefundene Artikel aus. Es werden keine im Backend gesetzten `Search it`-Einstellungen überschrieben.

## Modulausgabe

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
					echo '<span class="search_it-title"><a href="'. $hit_server . $article_hit->getUrl(['search_highlighter' => $request]) .'" title="'. $article_hit->getName() .'">'. $article_hit->getName() .'</a></span><br>';
					echo '<span class="search_it-teaser">'. $hit['highlightedtext'] .'</span><br>';
					echo '<span class="search_it-url"><a href="'. $hit_server . $article_hit->getUrl(['search_highlighter' => $request]) .'" title="'. $article_hit->getName() .'">'. urldecode($hit_server . $article_hit->getUrl()) .'</a></span>';
					echo '</li>';
				}
            }
        }
        echo '</ul><br>';

    }
	else if(!$result['count']) {
		echo '<p class="search_it-zero">Es wurden keine Suchergebnisse gefunden.</p>';
	}
	print "</section>";
}
?>
```

## CSS

Das Sucheingabe-Formular kann beliebig formatiert und mit Klassen ausgezeichnet werden. Das obige Beispiel ist aus dem kompletten Suchmodul entnommen. Es kann das CSS des Kompletten Suchmoduls verwendet werden.