# Suche über URLs aus dem URL Addon (>= Version 2.0)

Dieses Suchergebnis-Modul gibt Suchergebnisse aus dem URL Addon 2.0 oder größer aus. Um URLs aus dem URL Addon zu indexieren muss in den Einstellungen die Indexierung der URLs aus dem URL Addon aktiviert sein.

## Modulausgabe

```
<?php
$article_id = rex_article::getCurrentId();
$article = rex_article::get($article_id);
$request = rex_request('search', 'string', false);
$media_manager_type = "REX_VALUE[3]" ?: "rex_mediapool_preview";

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
            if($hit['type'] == 'url') {
				// Treffer aus dem URL Addon
				$url_sql = rex_sql::factory();
				$url_sql->setTable(rex::getTablePrefix() . \Url\UrlManagerSql::TABLE_NAME);
				$url_sql->setWhere("id = ". $hit['fid']);
				if ($url_sql->select('article_id, clang_id, profile_id, data_id, seo')) {
					$article_hit = rex_article::get($url_sql->getValue('article_id'));
					// Falls YCom Addon genutzt wird: zuerst Benuterrechte prüfen
					if(rex_addon::get('ycom')->isAvailable() == false || (rex_addon::get('ycom')->isAvailable() && rex_ycom_auth::articleIsPermitted($article_hit))) {
						$url_info = json_decode($url_sql->getValue('seo'), true);
						$url_profile = \Url\Profile::get($url_sql->getValue('profile_id'));

						// Falls YRewrite genutzt wird die korrekte Domain holen
						$hit_server = $server;
						if(rex_addon::get('yrewrite')->isAvailable()) {
							$hit_domain = rex_yrewrite::getDomainByArticleId($url_sql->getValue('article_id'), $url_sql->getValue('clang_id'));
							$hit_server = rtrim($hit_domain->getUrl(), "/");
						}
						
						$hit_link = $hit_server . rex_getUrl($url_sql->getValue('article_id'), $url_sql->getValue('clang_id'), [$url_profile->getNamespace() => $url_sql->getValue('data_id'), 'search_highlighter' => $request]);
						echo '<li class="search_it-result search_it-article">';
						echo '<span class="search_it-title"><a href="'. $hit_link .'" title="'. $url_info['title'] .'">'. $url_info['title'] .'</a></span><br>';
						$image = $url_info['image'] ? '<span class="search_it-previewimage"><img src="'. $hit_server .'/index.php?rex_media_type='. $media_manager_type .'&rex_media_file='. $url_info['image'] .'"></span>' : '';
						echo $image . '<span class="search_it-teaser">'. $hit['highlightedtext'] .'</span><br>';
						echo '<span class="search_it-url"><a href="'. $hit_link .'" title="'. $url_info['title'] .'">'. urldecode($hit_server.rex_getUrl($url_sql->getValue('article_id'), $url_sql->getValue('clang_id'), [$url_profile->getNamespace() => $url_sql->getValue('data_id')])) .'</a></span>';
						echo '</li>';
					}
				}
            }
			else {
                // Hier würde die Ausgabe weiterer Trefferarten folgen
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