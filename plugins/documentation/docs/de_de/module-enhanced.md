# Artikel-Suchergebnisse einschließlich Metadaten

Dieses Suchmodul bezieht weitere DB-Spalten in die Suche ein, z.B. den Artikel-Name und das SEO-Description-Feld des YRewrite-AddOns.

## Search it Einstellungen

In den `Search it`-Einstellungen müssen als Quelle folgende Datenbank-Spalten indexiert werden:

* `rex_article.name`
* `rex_article.yrewrite_description`

Außerdem sollte das maximale Trefferlimit auf 20 gestellt werden.

## Modulausgabe (ohne Erläuterungen)

```php
<section class="search_it-modul">
<p class="search_it-demotitle">[search_it] Suchergebnisse - Erweitertes Beispielmodul</p>
<?php
$server = rtrim(rex::getServer(),"/");
$request = rex_request('search', 'string', false);

if($request) {
    $search_it = new search_it();
    $result = $search_it->search($request);
	# dump($result); // Zum Debuggen ausgeben.

    if($result['count']) {
        echo '<h2 class="search_it-headline">{{ Suchergebnisse }}</h2>';

        echo '<ul class="search_it-results">';                          
        foreach($result['hits'] as $hit) {

            # dump($hit);
            if($hit['type'] == 'db_column' AND $hit['table'] == rex::getTablePrefix().'article'){
                $text = $hit['article_teaser'];
            } else {
                $text = $hit['highlightedtext'];
            }

            if(($hit['type'] == 'db_column' AND $hit['table'] == rex::getTablePrefix().'article') || ($hit['type'] == 'article'))
            {
                $article = rex_article::get($hit['fid']);
				$hit_server = $server;
				if(rex_addon::get('yrewrite')->isAvailable()) {
					$hit_domain = rex_yrewrite::getDomainByArticleId($hit['fid'], $hit['clang']);
					$hit_server = rtrim($hit_domain->getUrl(), "/");
				}
                echo '<li class="search_it-result search_it-article">
                          <p class="search_it-title">
                              <a href="'.$hit_server.$article->getUrl().'" title="'.$article->getName().'">'.$article->getName().'</a>
                          </p>
                          <p class="search_it-url">'.$hit_server.rex_getUrl($hit['fid'], $hit['clang'], []).'</p>
                          <p class="search_it-teaser">'.$text.'</p>
                      </li>';
            } else {                                  
               
               
                echo '<p class="search_it-missing_type">Das Suchergebnis vom Typ <i class="search_it-type">'.$hit['type'].' </i> kann nicht dargestellt werden.</p>';
            }
        }
        echo '</ul>';
    } else if(!$result['count']) {
        echo '<p class="search_it-zero">Die Suche nach <i class="search_it-request">'. rex_escape($request).' </i> ergab keine Treffer.</p>';
    }
}
    ?>
</section>
```

## Modulausgabe (mit Erläuterungen)

```php
<section class="search_it-modul">
<p class="search_it-demotitle">[search_it] Suchergebnisse - Erweitertes Beispielmodul</p>
<?php
$server = rtrim(rex::getServer(),"/"); // Aktuelle Website-Adresse ohne Slash am Ende
$request = rex_request('search', 'string', false); // GET/POST-Anfrage: Casting als String

if($request) { // Wenn ein Suchbegriff eingegeben wurde
    $search_it = new search_it(); // Suche initialisieren
    $result = $search_it->search($request); // Suche ausführen
	# dump($result); // Zum Debuggen ausgeben.

    if($result['count']) { // Wenn Ergebnisse vorhanden sind...
        echo '<h2 class="search_it-headline">{{ Suchergebnisse }}</h2>'; // Sprog-AddOn zur Übersetzung benutzen

        echo '<ul class="search_it-results">';                          
        foreach($result['hits'] as $hit) { // Jeder Treffer ein $hit

            # dump($hit); // Zum Debuggen ausgeben.
            if($hit['type'] == 'db_column' AND $hit['table'] == rex::getTablePrefix().'article'){
                $text = $hit['article_teaser'];
            } else {
                $text = $hit['highlightedtext'];
            }

            if(($hit['type'] == 'db_column' AND $hit['table'] == rex::getTablePrefix().'article') || ($hit['type'] == 'article'))
            {
                $article = rex_article::get($hit['fid']); // REDAXO-Artikel-Objekt holen

				// falls YRewrite genutzt wird
				$hit_server = $server;
				if(rex_addon::get('yrewrite')->isAvailable()) {
					$hit_domain = rex_yrewrite::getDomainByArticleId($hit['fid'], $hit['clang']);
					$hit_server = rtrim($hit_domain->getUrl(), "/");
				}

                echo '<li class="search_it-result search_it-article">
                          <p class="search_it-title">
                              <a href="'.$hit_server.$article->getUrl().'" title="'.$article->getName().'">'.$article->getName().'</a>
                          </p>
                          <p class="search_it-url">'.$hit_server.rex_getUrl($hit['fid'], $hit['clang'], []).'</p>
                          <p class="search_it-teaser">'.$text.'</p>
                      </li>'; // Ausgabe des Suchtreffers
            } else {                                  
                // Wenn der Treffer nicht aus REDAXO-Artikeln stammt, z.B., weil Medienpool oder Datenbankspalten
                // indiziert wurden. Siehe erweiterte Beispiele für die Ausgabe. Oder: Indexierung auf Artikel beschränken.
                echo '<p class="search_it-missing_type">Das Suchergebnis vom Typ <i class="search_it-type">'.$hit['type'].' </i> kann nicht dargestellt werden.</p>';
            }
        } // foreach($result['hits'] as $hit) END
        echo '</ul>';
    } else if(!$result['count']) { // Wenn keine Ergebnisse vorhanden sind....
        echo '<p class="search_it-zero">Die Suche nach <i class="search_it-request">'. rex_escape($request).' </i> ergab keine Treffer.</p>';
    }
} // if($request) END
    ?>
</section>
```

## CSS

Das Sucheingabe-Formular kann beliebig formatiert und mit Klassen ausgezeichnet werden. Das nachfolgende CSS formatiert das oben vorgegebene Beispiel.

```css
<style>
    /* Diese CSS-Datei in das Design ausschneiden und anpassen */
    .search_it-modul {
        box-sizing: border-box;
        font-size: 1rem;
        font-family: sans-serif;
        max-width: 640px;
        margin: 0 auto;
        border: 1px solid rgba(0,0,0,0.2);
        padding: 0 2rem 2rem 2rem;
    }
    .search_it-demotitle {
        font-size: 1.2rem;
        font-weight: bold;
        border-bottom: 1px solid  rgba(0,0,0,0.2);
        color: rgba(0,0,0,0.4);
        margin-bottom: 2rem;
    }
   
    .search_it-results {
        padding: 0;
        margin: 0;
    }
    .search_it-result {
        background: rgba(0,0,0,0.05);
        border: 1px solid rgba(0,0,0,0.4);
        padding: 1rem;
        margin: 1rem 0;
        list-style-type: none;
        list-style-position: inline;
    }
    .search_it-title,
    .search_it-title a {
        font-weight: bold;
        color: rgba(0,180,0,0.7);
    }

    .search_it-result .search_it-teaser {
        color: rgba(0,0,0,0.7);
    }
    .search_it-result .search_it-url {
        color: rgba(0,0,0,0.4);
    }
   
    .search_it-missing_type,
    .search_it-zero {
        background: rgba(180,0,0,0.05);
        padding: 1rem;
        border: 1px solid rgba(255,0,0,0.7);
        margin: 1rem 0;
        color: rgba(255,0,0,0.7);
    }
    .search_it-request,
    .search_it-type {
        font-weight: bold;
    }
</style>
```