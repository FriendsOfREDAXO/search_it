#Einsatz

Es wird hier ein komplettes Beispielmodule gezeigt. Das Modul erwartet den REQUEST-Parameter (also über GET oder POST) search_it.

##Eingabe Beispielmodul

```
<div class="row">
	<div class="col-xs-4">
		Anzahl Treffer pro Seite
	</div>
	<div class="col-xs-8">
		<input type="number" size="3" name="REX_INPUT_VALUE[1]" value="REX_VALUE[1]" min="10" max="100" class="form-control" />
	</div>
</div>
<div class="row">
	<div class="col-xs-12">&nbsp;</div>
</div>
<div class="row">
	<div class="col-xs-4">
		<input type="checkbox" name="REX_INPUT_VALUE[2]" value="true" <?php echo "REX_VALUE[2]" == 'true' ? ' checked="checked"' : ''; ?> style="float: right;" />
	</div>
	<div class="col-xs-8">
		Ähnlichkeitssuche aktivieren wenn keine Treffer gefunden werden?<br />
		<?php
			if(rex_config::get('search_it', 'similarwordsmode', 0) === 0) {
				print "<b>Die Ähnlichkeitssuche muss in den Search It Einstellungen aktiviert werden!</b>";
			}
		?>
	</div>
</div>
<div class="row">
	<div class="col-xs-12">&nbsp;</div>
</div>
<div class="row">
	<div class="col-xs-4">
		Anzuwendender Media Manager Typ bei Vorschaubildern:
	</div>
	<div class="col-xs-8">
		<select name="REX_INPUT_VALUE[3]" class="form-control">
		<?php
			$sql = rex_sql::factory();
			$selected = "REX_VALUE[3]" ?: "rex_mediapool_preview";
			$result = $sql->setQuery('SELECT name FROM ' . \rex::getTablePrefix() . 'media_manager_type ORDER BY status, name');
			for($i = 0; $i < $result->getRows(); $i++) {
				$name = $result->getValue("name");
				echo '<option value="'. $name .'" ';
	
				if ("REX_VALUE[3]" == $name) {
					echo 'selected="selected" ';
				}
				echo '>'. $name .'</option>';
				$result->next();
			}
		?>
		</select>
	</div>
</div>
```

##Ausgabe Beispielmodul

```
<?php
$article_id = rex_article::getCurrentId();
$article = rex_article::get($article_id);
$request = rex_request('search', 'string', false);
$limit = "REX_VALUE[1]" ?: 10;
$media_manager_type = "REX_VALUE[3]" ?: "rex_mediapool_preview";
$start = rex_request('start', 'int', 0);
?>

<section class="search_it-search">
	<form class="search_it-form" id="search_it-form1" action="<?php echo $article->getUrl(); ?>" method="get">
		<div class="search_it-flex">
			<?php
				echo '<input type="text" name="search" value="'. ($request ? rex_escape($request) : '') .'" placeholder="Suchbegriff eingeben" />';
			?>
			<button class="search_it-button" type="submit">
				<img src="<?php print rex_url::addonAssets('d2u_helper', 'icon_search.svg'); ?>">
			</button>
		</div>
	</form>
</section>

<?php
if($request) { // Wenn ein Suchbegriff eingegeben wurde
	$server = rtrim(rex::getServer(), "/");
	
	print '<section class="search_it-hits">';
	
	// Suche initialisieren (nur Artikel in der aktuellen Sprache)
    $search_it = new search_it(rex_clang::getCurrentId());
	// Limit für Pagination setzen
	$search_it->setLimit($start, $limit);
	// Suche in bestimmten Kategorien der Strukturverwaltung
//	$search_it->searchInCategories(array(5,6,13));
	// Zuerst die Suchergebnisse des URL Addon und der Artikel ausgeben, dann PDF und andere Dateien
	$search_it->setOrder(["field(texttype, 'url', 'article', 'file')" => "ASC"], true);
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
		echo $pagination;
		
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
            else if($hit['type'] == 'url') {
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
            else if($hit['type'] == 'file') {
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
                // Hier würde die Ausgabe weiterer Trefferarten folgen
            }
        }
        echo '</ul><br>';

		// Pagination
		echo $pagination;	
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

##Stylesheet Beispielmodul

```
.search_it-search {
	padding-bottom: 2em;
}
.search_it-button {
	position: absolute;
	right: 0;
	top: 0;
}
.search_it-flex {
	position: relative;
}
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
.search_it-teaser {
	
}
.search_it-url {
	
}

@font-face{
	font-family:'FontAwesome';
	src:url("./assets/addons/be_style/fonts/fontawesome-webfont.eot?v=4.7.0");
	src:url("./assets/addons/be_style/fonts/fontawesome-webfont.eot?#iefix&v=4.7.0") format('embedded-opentype'),
		url("./assets/addons/be_style/fonts/fontawesome-webfont.woff2?v=4.7.0") format('woff2'),
		url("./assets/addons/be_style/fonts/fontawesome-webfont.woff?v=4.7.0") format('woff'),
		url("./assets/addons/be_style/fonts/fontawesome-webfont.ttf?v=4.7.0") format('truetype'),
		url("./assets/addons/be_style/fonts/fontawesome-webfont.svg?v=4.7.0#fontawesomeregular") format('svg');
}
.icon {
	font-family: FontAwesome;
	font-weight: normal;
	line-height: 1em;
}
.file:before {
	color: darkred;
	content: "\f1c1";
}
.pdf:before {
	color: darkred;
	content: "f016";
}

.search_it-hits .pagination {
	height: 2em;
	margin: 0.5em 0;	
}
.search_it-hits .pagination li {
	background-color: article_color_box;
	height: 2em;
	margin: 0 0.5em 0.5em 0;
	text-align: center;
	width: 2.5em;
}
.search_it-hits .pagination li.current, .search_it-hits .pagination li:hover, .search_it-hits .pagination a:hover{
	background-color: article_color_h;
	color: article_color_bg;
	text-decoration: none;
	transition: background-color 300ms ease-out;
}

.search_it-highlight {
    color: darkorange;
}
.search_it-hits br {
	clear: both;
}
.search_it-previewimage {
	float: left;
	padding: 0 0.5em 0.5em 0;
}
```