# Beispiel-Module

## Suchergebnisse

Dieses Suchergebnis-Modul nimmt einen Suchbegriff mittels GET/POST-Parameter `search` entgegen und gibt gefundene Artikel bzw. URL-Addon-Profile inkl. Paginierung aus.

### Modulausgabe

```php
<section class="search_it-modul">
    <p class="search_it-demotitle">Suchergebnisse</p>

<?php
$server = rtrim(rex::getServer(),"/"); // Aktuelle Website-Adresse ohne Slash am Ende;
$searchterm = rex_request('search', 'string', false); // GET/POST-Anfrage: Casting als String
$limit = 10; // Anzahl Treffer pro Seite
$start = rex_request('start', 'int', 0);

if($searchterm) { // Wenn ein Suchbegriff eingegeben wurde
    $search_it = new search_it(rex_clang::getCurrentId()); // Suche initialisieren
    $search_it->setLimit($start, $limit); // Anzahl der Suchergebnisse auf $limit beschränken
    $result = $search_it->search($searchterm); // Suche ausführen

/* Ähnlichkeitssuche */
if ($result['count'] == 0 && count($result['simwords']) > 0) {

        $new_searchterm = $result['simwordsnewsearch'];
        $new_result = $search_it->search($new_searchterm);
        if($new_result['count'] > 0){
            $pagination = new rex_fragment();
            $pagination->setVar('searchterm', $newsearchString);
            echo $pagination->parse('search_it-result-simsearch.php');
            $result = $new_result;
        }

    }
/* Ähnlichkeitssuche ENDE */

    if($result['count']) { // Wenn Ergebnisse vorhanden sind...
?>

<h2 class="search_it-headline">Suchergebnisse</h2>
<ul class="search_it-results">

<?php

        /* Paginierung */
        $pagination = new rex_fragment();
        $pagination->setVar('searchterm', $searchterm);
        $pagination->setVar('limit', $limit);
        $pagination->setVar('start', $start);
        $pagination->setVar('result', $result);
        echo $pagination->parse('search_it-result-pagination.php');
        /* Paginierung  ENDE */

        /* Suchergebnis-Treffer ausgeben */
        foreach($result['hits'] as $hit) { // Jeder Treffer in $hit
            $fragment = new rex_fragment(); // Fragment zur Ausgabe des Treffers erstellen

            /* Treffer vom Typ REDAXO-Artikel */
            if($hit['type'] == 'article') {

                $article = rex_article::get($hit['fid']); // REDAXO-Artikel-Objekt holen

                // URL des Suchegebnis-Treffers, falls YRewrite genutzt wird
                if(rex_addon::get('yrewrite')->isAvailable()) {
                    $domain = rex_yrewrite::getDomainByArticleId($hit['fid'], $hit['clang']);
                    $url = rtrim($domain->getUrl(), "/");
                } else {
                    $url = $server.rex_getUrl($hit['fid'], $hit['clang'], array('search_highlighter' => $searchterm));
                }

                $fragment->setVar('title', $article->getName());
                $fragment->setVar('teaser', $hit['highlightedtext']);
                $fragment->setVar('url', $url);
                echo $fragment->parse('search_it-result-list-item.php');

            }
            /* Treffer vom Typ REDAXO-Artikel ENDE */
            /* Treffer vom Typ URL-Addon-Profil */
            else if($hit['type'] == 'url') {

                $url_sql = rex_sql::factory();
                $url_sql->setTable(search_it_getUrlAddOnTableName());
                $url_sql->setWhere("id = ". $hit['fid']);
                if ($url_sql->select('article_id, clang_id, profile_id, data_id, seo')) {
                    $url_info = json_decode($url_sql->getValue('seo'), true);
                    $url_profile = \Url\Profile::get($url_sql->getValue('profile_id'));

                    // get yrewrite article domain
                    if(rex_addon::get('yrewrite')->isAvailable()) {
                        $domain = rex_yrewrite::getDomainByArticleId($url_sql->getValue('article_id'), $url_sql->getValue('clang_id'));
                        $server = rtrim($domain->getUrl(), "/");
                    }
                    $url = $server . rex_getUrl($url_sql->getValue('article_id'), $url_sql->getValue('clang_id'), [$url_profile->getNamespace() => $url_sql->getValue('data_id'), 'search_highlighter' => $searchterm]);

                    $fragment->setVar('title', $url_info['title']);
                    $fragment->setVar('teaser', $hit['highlightedtext']);
                    $fragment->setVar('url',url);

                    echo $fragment->parse('search_it-result-list-item.php');
                }

            }
            /* Treffer vom Typ URL-Addon-Profil ENDE */
            /* Treffer im Index, der separat abgefangen werden muss */
            else {
                $fragment->setVar('type',$hit['type']);
                echo $fragment->parse('search_it-result-list-missing-type.php');
            }
            /* Treffer im Index, der separat abgefangen werden muss ENDE */

        }
        /* Suchergebnis-Treffer ausgeben ENDE */

?>

</ul>

<?php
    /* Paginierung */
    echo $pagination->parse('search_it-result-pagination.php');
    /* Paginierung  ENDE */

    }  else { // Wenn keine Suchergebnisse vorhanden sind....
        $fragment = new rex_fragment();
        $fragment->setVar('searchterm', $searchterm);
        echo $fragment->parse('search_it-result-no-result.php');
    }
}
?>

</section>
```

### Beispiel-CSS

Das Sucheingabe-Formular kann beliebig formatiert und mit Klassen ausgezeichnet werden. Das nachfolgende CSS formatiert die nachfolgenden Beispiele und kann als Blaupause für eigenes CSS verwendet werden.

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

## Ähnlichkeitssuche

### Voraussetzungen

Die Ähnlichkeitssuche muss in den AddOn-Einstellungen aktiviert sein.

Search it baut anschließend bei jeder Suche, die einen Treffer ergab, einen Schlagwortindex aus. Dabei wird angenommen, dass Wörter, die zu Suchergebnissen führen, richtig geschrieben sind.

Sollte eine Suche keine Ergebnisse liefern, füllt Search it das Result-Array mit eventuell gefundenen ähnlichen Wörtern und macht auch einen Vorschlag, wie der neue Suchbegriff aussehen könnte.

> Tipp: Um die Ähnlichkeitssuche effektiv einsetzen zu können, empfiehlt es sich, die Suche selbst mit richtigen Schlagwörtern zu füttern. Dadurch sind erste Suchwörter indexiert und die Ähnlichkeitssuche kann bei einer falschen Schreibweise dieser Wörter diese vorschlagen.
> Tipp: Die durchgeführte Ähnlichkeitssuche gibt bei der Ergebnis-Rückgabe zusätzliche Informationen zurück, bspw., ob sie überhaupt angewendet wurde, welche Begriffe berücksichtigt wurden u.a.

## Artikel-Suchergebnisse inkl. Metadaten

Dieses Suchmodul bezieht weitere DB-Spalten in die Suche ein, z.B. den Artikel-Name und das SEO-Description-Feld des YRewrite-AddOns.

### Voraussetzungen

In den `Search it`-Einstellungen müssen als Quelle folgende Datenbank-Spalten indexiert werden:

* `rex_article.name`
* `rex_article.yrewrite_description`

Außerdem sollte das maximale Trefferlimit auf 20 gestellt werden.

### Modulausgabe-Beispiel

```php
<section class="search_it-modul">
<p class="search_it-demotitle">[search_it] Suchergebnisse - Erweitertes Beispielmodul</p>
<?php
$server = rtrim(rex::getServer(),"/"); // Aktuelle Website-Adresse ohne Slash am Ende
$searchterm = rex_request('search', 'string', false); // GET/POST-Anfrage: Casting als String

if($searchterm) { // Wenn ein Suchbegriff eingegeben wurde
    $search_it = new search_it(); // Suche initialisieren
    $result = $search_it->search($searchterm); // Suche ausführen
    # dump($result); // Zum Debuggen ausgeben.

    if($result['count']) { // Wenn Ergebnisse vorhanden sind...
        echo '<h2 class="search_it-headline">Suchergebnisse</h2>';

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
        echo '<p class="search_it-zero">Die Suche nach <i class="search_it-request">'. rex_escape($searchterm).' </i> ergab keine Treffer.</p>';
    }
} // if($searchterm) END
    ?>
</section>
```

## Medien-Metasuche

Dieses Suchergebnis-Modul nimmt einen Suchbegriff mittels GET/POST-Parameter `search` entgegen und gibt gefundene Medien aus.

### Search it Einstellungen

In den `Search it`-Einstellungen müssen als Quelle folgende Datenbank-Spalten indexiert werden:

* `rex_media.title`
* `rex_media.filename`
* `rex_media.fileext`
* `rex_media.med_description`

### Modulausgabe-Beispiel

```php
<section class="search_it-modul">
    <p class="search_it-demotitle">[search_it] Suchergebnisse - Bildersuche</p>
    <?php
$server = rtrim(rex::getServer(),"/"); // Aktuelle Website-Adresse ohne Slash am Ende;
$searchterm = rex_request('search', 'string', false); // GET/POST-Anfrage: Casting als String

if($searchterm) { // Wenn ein Suchbegriff eingegeben wurde
    $search_it = new search_it(); // Suche initialisieren
    $search_it->searchInDbColumn(rex::getTablePrefix().'media','title');
    $search_it->searchInDbColumn(rex::getTablePrefix().'media','filename');
    $search_it->searchInDbColumn(rex::getTablePrefix().'media','fileext');
    $search_it->searchInDbColumn(rex::getTablePrefix().'media','med_description');
    $result = $search_it->search($searchterm); // Suche ausführen

    if($result['count']) { // Wenn Ergebnisse vorhanden sind...
        echo '<h2 class="search_it-headline">Suchergebnisse</h2>'; // Sprog-AddOn zur Übersetzung benutzen

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
        echo '<p class="search_it-zero">Die Suche nach <i class="search_it-request">'.rex_escape($searchterm).' </i> ergab keine Treffer.</p>';
    }
} // if($searchterm) END
    ?>
</section>
```

## Komplexe Suchergebnisse

> Hinweis: Dieses Beispiel wurde noch nicht für Search_it portiert. Du kannst helfen, diese Anleitung zu korrigieren: <https://github.com/FriendsOfREDAXO/search_it/>

Dieses Beispielmodul ähnelt dem Beispiel: Modul zur Suche mit PDF-Dateien, Paginierung und Ähnlichkeitssuche. Die Ähnlichkeitssuche sollte aktiviert, sowie bei der Dateisuche die Option "Medienpool indexieren" ausgewählt sein. Außerdem sollte in dem Feld für die Dateiendungen nur "pdf" stehen.

Ein erweitertes Suchformular bietet dem Nutzer an, folgende Punkte auszuwählen:

* Suchmodus (AND oder OR)
* Suchen in (Kategorieauswahl)
* Wieviele Ergebnisse pro Seite?

```php
<form id="search-form" method="get" action="<?php echo rex_geturl(REX_ARTICLE_ID, REX_CLANG_ID, array(), '&'); ?>">

    <fieldset>
    <legend>Suchformular</legend>

    <input type="hidden" name="article_id" value="REX_ARTICLE_ID" />
    <input type="hidden" name="clang" value="REX_CLANG_ID" />

    <p><label for="searchterm">Suchbegriff:</label>
    <input type="text" id="searchterm" name="searchterm" value="<?php echo rex_escape(rex_request('searchterm', 'string', '')); ?>" /></p>

    <p><label for="logicalmode">Suchmodus:</label>
    <select id="logicalmode" name="logicalmode">
        <option value="and"<?php if(rex_request('logicalmode', 'string', 'and') == 'and') echo ' selected="selected"'; ?>>Suchergebnis muss alle Wörter enthalten</option>
        <option value="or"<?php if(rex_request('logicalmode', 'string', 'and') == 'or') echo ' selected="selected"'; ?>>Suchergebnis muss mindestens ein Wort enthalten</option>
    </select></p>

    <p><label for="searchin">Suchen in:</label>

<?php $cat_select = new rex_category_select(true, REX_CLANG_ID, false, false); $cat_select->setAttribute('id', 'searchin'); $cat_select->setAttribute('name', 'searchin[]'); $cat_select->setAttribute('multiple', 'multiple'); $cat_select->setAttribute('size', '10'); $cat_select->setSelected(rex_request('searchin', 'array', array())); $cat_select->show(); ?></p>

    <p><input type="checkbox" value="1" name="subcats" id="subcats"<?php if(rex_request('subcats', 'int', 0)) echo ' checked="checked"'; ?> /><label for="subcats">Unterkategorien in die Suche einschließen</label></p>

    <p><label for="resultcount">Ergebnisse pro Seite:</label>
    <select id="resultcount" name="resultcount">

<?php $resultcount = rex_request('resultcount', 'int', 10); foreach(array(10,20,50,100) as $option)

    echo '    <option value="'.$option.'"'.($resultcount==$option?' selected="selected"':'').'>'.$option.'</option>'."\n";

?>

    </select></p>

    <p><input type="submit" id="submit" value="Suche starten" /></p>
    </fieldset>

</form>
```

In dem Modul zur Präsentation der Suchergebnisse werden die entsprechenden Einstellungen an Search it übergeben, die Suche ausgeführt und letztendlich die Suchergebnisse ausgegeben.

```php
<?php

    $searchterm = rex_request('searchterm', 'string', '');
    $logicalmode = rex_request('logicalmode', 'string', 'and');
    $showmax = rex_request('resultcount', 'int', 10);
    $searchinIDs = rex_request('searchin', 'array', array());
    $traverseSubcats = rex_request('subcats', 'bool', false);

    if(!empty($searchterm)){
    $search_it = new search_it();
    $search_it->setLimit(array($start = rex_get('start', 'int', 0), $showmax));
    $search_it->setLogicalMode($logicalmode);
    if($traverseSubcats){
        $search_it->searchInCategories(a587_getCategories(true, true, $searchinIDs));
    } else {
        $search_it->searchInCategories($searchinIDs);
    }

    $result = $search_it->search($searchterm);
    if(!$result['count'] AND count($result['simwords']) > 0){
        $newsearchString = $result['simwordsnewsearch'];
        $result = $search_it->search($newsearchString);
        if($result['count'] > 0){
        echo '<p>Meinten Sie <strong>'.$newsearchString.'</strong>?</p>';
        }
    }

    if($result['count'] > 0){
        echo '<ul class="searchresults">';
        foreach($result['hits'] as $hit){
        switch($hit['type']){
            case 'file':
            $text = $hit['highlightedtext'];

            // PDF-Datei
            $filename = explode('/', $hit['filename']);
            $pdf = rex_media::get($filename[count($filename)-1]);

            echo '    <li class="pdf">
        <h4><a href="'.rex_escape($pdf->getFullPath()).'">'.$pdf->getOrgFileName().'</a></h4>
        <p class="highlightedtext">'.$text.'</p>
        <p class="url">'.rex::getServer().'files/'.$pdf->getOrgFileName().'</p>
    </li>';
            break;

            case 'db_column':
            case 'article':
            if($hit['type'] == 'db_column'){
                $text = $hit['article_teaser'];
                if($hit['table'] == rex::getTablePrefix().'article')
                $hit['fid'] = $hit['values']['id'];
            } else {
                $text = $hit['highlightedtext'];
            }

            // Artikel oder DB-Spalte aus der Artikel-Tabelle
            $article = rex_article::get($hit['fid']);

            echo '    <li>
        <h4><a href="'.rex_escape($article->getUrl()).'">'.$article->getName().'</a></h4>
        <p class="highlightedtext">'.$text.'</p>
        <p class="url">'.rex::getServer().rex_getUrl($hit['fid'], $hit['clang']).'</p>
    </li>';
            break;

        }
        }
        echo '</ul>';
        }
    } else {
        echo '<em>Leider nichts gefunden.</em>';
    }
    }

?>
```
