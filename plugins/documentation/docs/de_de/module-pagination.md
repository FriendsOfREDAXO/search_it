# Suche mit Pagination

Für umfangreiche Webauftritte kann eine Pagination für die Suchergebnisse sinnvoll oder notwendig sein.

```php
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
