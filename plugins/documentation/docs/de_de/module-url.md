# Suche über URLs aus dem URL Addon (>= Version 2.0)

Dieses Suchergebnis-Modul gibt Suchergebnisse aus dem URL Addon 2.0 oder größer aus. Um URLs aus dem URL Addon zu indexieren muss in den Einstellungen die Indexierung der URLs aus dem URL Addon aktiviert sein.

## Modulausgabe

```php
$article_id = rex_article::getCurrentId();
$request = rex_request('search', 'string', false);

if($request) { // Wenn ein Suchbegriff eingegeben wurde
	$server = rtrim(rex::getServer(), "/");

	print '<section class="search_it-hits">';

	// Init search and execute
    $search_it = new search_it();
    $result = $search_it->search($request);

	echo '<h2 class="search_it-headline">Suchergebnisse</h2>';
	if($result['count']) {

		echo '<ul class="search_it-results">';
		foreach($result['hits'] as $hit) {
			if($hit['type'] == 'url') {
				// url hits
				$url_sql = rex_sql::factory();
				$url_sql->setTable(search_it_getUrlAddOnTableName());
				$url_sql->setWhere("url_hash = '". $hit['fid'] ."'");
				if ($url_sql->select('article_id, clang_id, profile_id, data_id, seo')) {
					if($url_sql->getRows() > 0) {
						$url_info = json_decode($url_sql->getValue('seo'), true);
						$url_profile = \Url\Profile::get($url_sql->getValue('profile_id'));

						// get yrewrite article domain
						$hit_server = $server;
						if(rex_addon::get('yrewrite')->isAvailable()) {
							$hit_domain = rex_yrewrite::getDomainByArticleId($url_sql->getValue('article_id'), $url_sql->getValue('clang_id'));
							$hit_server = rtrim($hit_domain->getUrl(), "/");
						}

						$hit_link = $hit_server . rex_getUrl($url_sql->getValue('article_id'), $url_sql->getValue('clang_id'), [$url_profile->getNamespace() => $url_sql->getValue('data_id'), 'search_highlighter' => $request]);
						echo '<li class="search_it-result search_it-article">';
						echo '<span class="search_it-title">';
						echo '<a href="'. $hit_link .'" title="'. $url_info['title'] .'">'. $url_info['title'] .'</a>';
						echo '</span><br>';
						echo '<span class="search_it-teaser">'. $hit['highlightedtext'] .'</span><br>';
						echo '<span class="search_it-url"><a href="'. $hit_link .'" title="'. $url_info['title'] .'">'.$hit_server.rex_getUrl($url_sql->getValue('article_id'), $url_sql->getValue('clang_id'), [$url_profile->getNamespace() => $url_sql->getValue('data_id')]).'</a></span>';
						echo '</li>';
					}
				}
			}
			else {
                // other hit types
            }
        }
        echo '</ul>';
    }
	else if(!$result['count']) {
		echo '<p class="search_it-zero">Keine Suchergebnisse gefunden.</p>';
	}
	print "</section>";
}
```

## CSS

Das Sucheingabe-Formular kann beliebig formatiert und mit Klassen ausgezeichnet werden. Das nachfolgende CSS formatiert das oben vorgegebene Beispiel.

```css
<style>
    /* Diese CSS-Datei in das Design ausschneiden und anpassen */
	.search_it-headline, .search_it-result {
		margin: 0.75em 0 0.75em 0;
	}
	.search_it-result {
		background-color: article_color_box;
		padding: 15px;
	}
	.search_it-title {
		font-size: 1.25rem;
		font-weight: bold;
	}
</style>
```
