# Weitere RexSearch-Beispiel-Module

## Suche mit Pagination

Für umfangreiche Webauftritte kann eine Pagination für die Suchergebnisse sinnvoll oder notwendig sein.

```
        <?php
		$article_id = rex_article::getCurrentId();
		$request = rex_request('search', 'string', false);
		$limit = 10; // Anzahl Treffer pro Seite
		$start = rex_request('start', 'int', 0);
        
		if($request) { // Wenn ein Suchbegriff eingegeben wurde
			$server = rtrim(rex::getServer(), "/");
			
			print '<section class="search_it-hits">';
			
			// Init search and execute
			$search_it = new search_it();
			$search_it->setLimit($start, $limit);
			$result = $search_it->search($request);

			if($result['count']) {
				echo '<h2 class="search_it-headline">Suchergebnisse</h2>';

		 		// Pagination
				$pagination = "";
				if($result['count'] > $limit) {
					$self = rex_article::get($article_id);
					$pagination = '<ul class="pagination">';
					for($i = 0; ($i * $limit) < $result['count']; $i++){
						if(($i * $limit) == $start){
							$pagination .= '<li class="current">'. ($i + 1) .'</li>';
						}
						else {
							$pagination .= '<li><a href="'.$self->getUrl(array('search' => $request, 'start' => $i * $limit)).'">'. ($i + 1) .'</a></li>';
						}
					}
					$pagination .= '</ul>';
				}
				echo $pagination; // Pagination vor den Suchergebnissen ausgeben
				
				echo '<ul class="search_it-results">';                           
				foreach($result['hits'] as $hit) {
					// Hier werden die Suchergebnisse ausgegeben um den Code einfach zu halten wurde dieser Teil entfernt
				}
				echo '</ul>';

				echo $pagination; // Pagination nach den Suchergebnissen ausgeben
			}
			print "</section>";
		}
        
        ?>
```

## Ähnlichkeitssuche

Dieses Beispielmodul erweitert eine Suche um die Suche nach ähnlichen Wörtern. Wichtig ist dabei, dass die Ähnlichkeitssuche im Backend aktiviert ist.
```
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
## Suche mit PDF-Dateien, Pagination und Ähnlichkeitssuche

Dieses Beispielmodul erweitert das Paginationsmodul und die Ähnlichkeitssuche um die Suche von PDF-Dateien aus dem Medienpool. Die Ähnlichkeitssuche sollte aktiviert, sowie bei der Dateisuche die Option "Medienpool indexieren" ausgewählt sein. Außerdem sollte in dem Feld für die Dateiendungen nur "pdf" stehen.

```
<?php
$article_id = rex_article::getCurrentId();
$request = rex_request('search', 'string', false);
$limit = "REX_VALUE[1]" ?: 10;
$start = rex_request('start', 'int', 0);

if($request) { // Wenn ein Suchbegriff eingegeben wurde
	$server = rtrim(rex::getServer(), "/");
	
	print '<section class="search_it-hits">';
	
	// Init search and execute
    $search_it = new search_it();
	$search_it->setLimit($start, $limit);
    $result = $search_it->search($request);

	echo '<h2 class="search_it-headline">Suchergebnisse</h2>';
	if($result['count'] == 0 && count($result['simwords']) > 0){
		// similarity search
		$newsearchString = $result['simwordsnewsearch'];
		$result_simwords = $search_it->search($newsearchString);
		if($result_simwords['count'] > 0){
			echo '<p>Meinten Sie <strong>'. $newsearchString .'</strong>?</p>';
		}
	}

	if($result['count']) {
 		// Pagination
		$pagination = "";
		if($result['count'] > $limit) {
			$self = rex_article::get($article_id);
			$pagination = '<ul class="pagination">';
			for($i = 0; ($i * $limit) < $result['count']; $i++){
				if(($i * $limit) == $start){
					$pagination .= '<li class="current">'. ($i + 1) .'</li>';
				}
				else {
					$pagination .= '<li><a href="'.$self->getUrl(array('search' => $request, 'start' => $i * $limit)).'">'. ($i + 1) .'</a></li>';
				}
			}
			$pagination .= '</ul>';
		}
		echo $pagination;
		
		echo '<ul class="search_it-results">';                           
        foreach($result['hits'] as $hit) {

            if($hit['type'] == 'article') {
				// article hits
                $article = rex_article::get($hit['fid']);
				
				// get article domain
				$hit_server = $server;
				if(rex_addon::get('yrewrite')->isAvailable()) {
					$hit_domain =  rex_yrewrite::getDomainByArticleId($hit['fid'], $hit['clang']);
					$hit_server = rtrim($hit_domain->getUrl(), "/");
				}
				
				$hit_link = $hit_server . rex_getUrl($hit['fid'], $hit['clang'], array('search_highlighter' => $request));
                echo '<li class="search_it-result search_it-article">';
				echo '<span class="search_it-title">';
                echo '<a href="'. $hit_link .'" title="'. $article->getName() .'">'. $article->getName() .'</a>';
				echo '</span><br>';
				echo '<span class="search_it-teaser">'. $hit['highlightedtext'] .'</span><br>';
				echo '<span class="search_it-url"><a href="'. $hit_link .'" title="'. $article->getName() .'">'.$hit_server.rex_getUrl($hit['fid'], $hit['clang']).'</a></span>';
				echo '</li>';
            }
            else if($hit['type'] == 'file') {
				// pdf hits
				$media = rex_media::get(pathinfo($hit['filename'], PATHINFO_BASENAME));
				if(is_object($media)) { 
					$hit_link = $server . rex_url::media($media->getFileName());
					echo '<li class="search_it-result search_it-image search_it-flex">';
					echo '<span class="search_it-title">';
					echo '<a href="'. $hit_link .'" title="'. $media->getTitle() .'">';
					echo '<span class="icon '. ($filetype == 'pdf' ? 'pdf' : 'file') .'"></span>';
					echo '&nbsp;&nbsp;'. $media->getTitle() .'</a>';
					echo '</span><br>';
					echo '<span class="search_it-teaser">'. $hit['highlightedtext'] .'</span><br>';
					echo '<span class="search_it-url"><a href="'. $hit_link .'" title="'. $media->getTitle() .'">'. $hit_link .'</a></span>';
					echo '</li>'; 
				}
			}
			else {
                // other hit types
            }
        }
        echo '</ul>';

		// Pagination
		echo $pagination;	
    }
	else if(!$result['count']) {
		echo '<p class="search_it-zero">Keine Suchergebnisse gefunden.</p>';
	}
	print "</section>";
}
```
