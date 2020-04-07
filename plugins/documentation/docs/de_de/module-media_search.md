# Einfache Artikel-Suchergebnisse

Dieses Suchergebnis-Modul nimmt einen Suchbegriff mittels GET/POST-Parameter `search` entgegen und gibt gefundene Medien aus. 

## Search it Einstellungen

In den `Search it`-Einstellungen müssen als Quelle folgende Datenbank-Spalten indexiert werden:

* `rex_media.title`
* `rex_media.filename`
* `rex_media.fileext`
* `rex_media.med_description`

Außerdem sollte das maximale Trefferlimit auf 20 gestellt werden.

## Modulausgabe

```
<?php
$article_id = rex_article::getCurrentId();
$article = rex_article::get($article_id);
$request = rex_request('search', 'string', false);
$limit = "REX_VALUE[1]" ?: 10;
$media_manager_type = "REX_VALUE[3]" ?: "rex_mediapool_preview";
$start = rex_request('start', 'int', 0);

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
            if($hit['type'] == 'file') {
				// Treffer aus dem Medienpool deren Inhalt durchsucht werden kann, z.B. PDF Dateien
				$media = rex_media::get(pathinfo($hit['filename'], PATHINFO_BASENAME));
				if(is_object($media)) { 
					$has_permission = FALSE;
					// Falls YCom Auth Media Plugin genutzt wird: zuerst Benuterrechte prüfen
					if(rex_plugin::get('ycom', 'media_auth')->isAvailable()) {
						$has_permission = rex_ycom_media_auth::checkPerm(rex_media_manager::create(null, pathinfo($hit['filename'], PATHINFO_BASENAME)));
					}
					if($has_permission) {
						$hit_link = $server . rex_url::media($media->getFileName());
						echo '<li class="search_it-result search_it-image search_it-flex">';
						echo '<span class="search_it-title"><a href="'. $hit_link .'" title="'. $media->getTitle() .'">'. ($filetype == 'pdf' ? '<span class="icon pdf"></span>' : '');
						echo '&nbsp;&nbsp;'. $media->getTitle() .'</a></span><br>';
						$image = substr($media->getType(), 0, 5) === "image" ? '<span class="search_it-previewimage"><img src="index.php?rex_media_type='. $media_manager_type .'&rex_media_file='. $media->getFileName() .'"></span>' : '';
						echo $image .'<span class="search_it-teaser">'. $hit['highlightedtext'] .'</span><br>';
						echo '<span class="search_it-url"><a href="'. $hit_link .'" title="'. $media->getTitle() .'">'. $hit_link .'</a></span>';
						echo '</li>';
					}
				}
			}
            else if($hit['type'] == 'db_column') {
				// Treffer von Bildern aus dem Medienpool
				if($hit['table'] == rex::getTablePrefix() .'media' && isset($hit['values']['filetype']) && substr($hit['values']['filetype'], 0, 5) === "image") {
					$media = rex_media::get($hit['values']['filename']);
					if(is_object($media)) { 
						$has_permission = FALSE;
						if(rex_plugin::get('ycom', 'media_auth')->isAvailable()) {
							$has_permission = rex_ycom_media_auth::checkPerm(rex_media_manager::create(null, $media->getFileName()));
						}
						if($has_permission) {
							$hit_link = $server . rex_url::media($media->getFileName());
							echo '<li class="search_it-result search_it-image search_it-flex">';
							echo '<span class="search_it-title"><a href="'. $hit_link .'" title="'. $media->getTitle() .'">'. ($media->getTitle() ?: $media->getFileName()) .'</a></span><br>';
							$image = substr($media->getType(), 0, 5) === "image" ? '<span class="search_it-previewimage"><img src="'. $server .'/index.php?rex_media_type='. $media_manager_type .'&rex_media_file='. $media->getFileName() .'"></span>' : '';
							echo $image .'<span class="search_it-teaser">'. $hit['highlightedtext'] .'</span><br>';
							echo '<span class="search_it-url"><a href="'. $hit_link .'" title="'. $media->getTitle() .'">'. $hit_link .'</a></span>';
							echo '</li>';
						}
					}
				}
				// Hier würden die Ausgabe weitere Tabellentreffer folgen
			}
			else {
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