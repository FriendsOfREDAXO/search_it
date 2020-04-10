# Einfache Artikel-Suchergebnisse

Dieses Suchergebnis-Modul nimmt einen Suchbegriff mittels GET/POST-Parameter `search` entgegen und gibt gefundene Medien aus.

## Search it Einstellungen

In den `Search it`-Einstellungen müssen als Quelle folgende Datenbank-Spalten indexiert werden:

* `rex_media.title`
* `rex_media.filename`
* `rex_media.fileext`
* `rex_media.med_description`

Außerdem sollte das maximale Trefferlimit auf 20 gestellt werden.

## Modulausgabe (ohne Erläuterungen)

```php
<section class="search_it-modul">
    <p class="search_it-demotitle">[search_it] Suchergebnisse - Bildersuche</p>
    <?php
$server = rtrim(rex::getServer(),"/");
$request = rex_request('search', 'string', false);

if($request) {
    $search_it = new search_it();
    $search_it->searchInDbColumn(rex::getTablePrefix().'media','title');
    $search_it->searchInDbColumn(rex::getTablePrefix().'media','filename');
    $search_it->searchInDbColumn(rex::getTablePrefix().'media','fileext');
    $search_it->searchInDbColumn(rex::getTablePrefix().'media','med_description');
    $result = $search_it->search($request);
    # echo "<pre><code>"; print_r($result); echo "</code></pre>";

    if($result['count']) {
        echo '<h2 class="search_it-headline">{{ Suchergebnisse }}</h2>';

        echo '<ul class="search_it-results">';                          
        foreach($result['hits'] as $hit) {

            # echo "<pre><code>"; print_r($hit); echo "</code></pre>";
            $media = rex_media::get($hit['values']['filename']);
            if(is_object($media)) {
                echo '<li class="search_it-result search_it-image search_it-flex">
                          <p class="search_it-img">
                              <a href="'.$server.'/media/'.$media->getFileName().'" title="'.$media->getTitle().'"><img src="'.$server.'/images/mediamanagerprofile/'.$media->getFileName().'" alt="'.$media->getTitle().'" /></a>
                          </p>
                          <p class="search_it-title">
                              <a href="'.$server.'/media/'.$media->getFileName().'" title="'.$media->getTitle().'">'.$media->getTitle().'</a>
                          </p>
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
    <p class="search_it-demotitle">[search_it] Suchergebnisse - Bildersuche</p>
    <?php
$server = rtrim(rex::getServer(),"/"); // Aktuelle Website-Adresse ohne Slash am Ende;
$request = rex_request('search', 'string', false); // GET/POST-Anfrage: Casting als String

if($request) { // Wenn ein Suchbegriff eingegeben wurde
    $search_it = new search_it(); // Suche initialisieren
    $search_it->searchInDbColumn(rex::getTablePrefix().'media','title');
    $search_it->searchInDbColumn(rex::getTablePrefix().'media','filename');
    $search_it->searchInDbColumn(rex::getTablePrefix().'media','fileext');
    $search_it->searchInDbColumn(rex::getTablePrefix().'media','med_description');
    $result = $search_it->search($request); // Suche ausführen
    # echo "<pre><code>"; print_r($result); echo "</code></pre>"; // Zum Debuggen ausgeben.

    if($result['count']) { // Wenn Ergebnisse vorhanden sind...
        echo '<h2 class="search_it-headline">{{ Suchergebnisse }}</h2>'; // Sprog-AddOn zur Übersetzung benutzen

        echo '<ul class="search_it-results">';                          
        foreach($result['hits'] as $hit) { // Jeder Treffer ein $hit

            # echo "<pre><code>"; print_r($hit); echo "</code></pre>"; // Zum Debuggen ausgeben.
            $media = rex_media::get($hit['values']['filename']);
            if(is_object($media)) { // Todo: fileext prüfen, benötigt github-fix
                echo '<li class="search_it-result search_it-image search_it-flex">
                          <p class="search_it-img">
                              <a href="'.$server.'/media/'.$media->getFileName().'" title="'.$media->getTitle().'"><img src="'.$server.'/images/mediamanagerprofile/'.$media->getFileName().'" alt="'.$media->getTitle().'" /></a>
                          </p>
                          <p class="search_it-title">
                              <a href="'.$server.'/media/'.$media->getFileName().'" title="'.$media->getTitle().'">'.$media->getTitle().'</a>
                          </p>
                      </li>'; // Ausgabe des Suchtreffers
            } else {                                  
                // Wenn der Treffer nicht aus REDAXO-Artikeln stammt, z.B., weil Medienpool oder Datenbankspalten
                // indiziert wurden. Siehe erweiterte Beispiele für die Ausgabe. Oder: Indexierung auf Artikel beschränken.
                echo '<p class="search_it-missing_type">Das Suchergebnis vom Typ <i class="search_it-type">'.$hit['type'].' </i> kann nicht dargestellt werden.</p>';
            }
        } // foreach($result['hits'] as $hit) END
        echo '</ul>';
    } else if(!$result['count']) { // Wenn keine Ergebnisse vorhanden sind....
        echo '<p class="search_it-zero">Die Suche nach <i class="search_it-request">'.rex_escape($request).' </i> ergab keine Treffer.</p>';
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
    .search_it-image img {
        max-width: 100%;
        display: block;
    }

    .search_it-image .search_it-flex {
        display: flex;  
    }
    .search_it-image .search_it-flex > * {
        padding: 1rem;
        flex: 1 1 200px;  
    }
</style>
```