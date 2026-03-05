# Search It — REDAXO Volltextsuche

Search It ist ein REDAXO CMS Addon für die Volltextsuche im Frontend. Es indexiert Artikel, Medien, Dateien (inkl. PDF), URL-Addon-URLs und beliebige Datenbank-Spalten.

## Schnellstart: Suche einbauen

### 1. Suchformular (REDAXO-Modul Input)

```html
<form action="<?= rex_getUrl(REX_VALUE[1]) ?>" method="get">
    <input type="text" name="search" value="<?= rex_escape(rex_request('search', 'string', '')) ?>">
    <button type="submit">Suchen</button>
</form>
```

`REX_VALUE[1]` = Artikel-ID der Ergebnisseite.

### 2. Suchergebnisse ausgeben (REDAXO-Modul Output)

```php
use FriendsOfRedaxo\SearchIt\SearchIt;

$searchTerm = rex_request('search', 'string', '');
if ($searchTerm !== '') {
    $search = new SearchIt();
    $result = $search->search($searchTerm);

    if ($result['count'] > 0) {
        foreach ($result['hits'] as $hit) {
            // Artikel-Treffer
            if ($hit['type'] === 'article') {
                $article = rex_article::get($hit['fid'], $hit['clang']);
                if ($article) {
                    echo '<h3><a href="' . rex_getUrl($hit['fid'], $hit['clang']) . '">'
                        . rex_escape($article->getName()) . '</a></h3>';
                    echo '<p>' . $hit['highlightedtext'] . '</p>';
                }
            }
        }
    } else {
        echo '<p>Keine Ergebnisse gefunden.</p>';
    }
}
```

## SearchIt Klasse — API-Referenz

Namespace: `FriendsOfRedaxo\SearchIt\SearchIt`
Backward-kompatibel als `search_it` (deprecated).

### Konstruktor

```php
$search = new SearchIt(int|false $clang = false, bool $loadSettings = true, bool $useStopwords = true);
```

- `$clang` — Sprach-ID oder `false` für alle Sprachen
- `$loadSettings` — Addon-Einstellungen laden (Standard: ja)
- `$useStopwords` — Deutsche Stoppwörter verwenden

### Suche ausführen

```php
$result = $search->search(string $searchTerm): array;
```

Gibt ein Array zurück — siehe [Ergebnis-Array](#ergebnis-array).

### Ergebnisse einschränken

```php
// Nur in bestimmten Artikeln suchen
$search->searchInArticles([1, 5, 12]);

// Nur in bestimmten Kategorien suchen
$search->searchInCategories([3, 7]);

// Kategorie-Baum (inkl. Unterkategorien)
$search->searchInCategoryTree(3);

// Nur in bestimmten Medienpool-Kategorien
$search->searchInFileCategories([2, 4]);

// Nur in bestimmten DB-Spalten
$search->searchInDbColumn('rex_article', 'name');

// Eigene WHERE-Bedingung
$search->setWhere("texttype = 'article'");
```

### Ergebnis-Darstellung konfigurieren

```php
// Highlighting-Tags (Standard: <mark>...</mark>)
$search->setSurroundTags('<strong>', '</strong>');

// Highlighting-Typ: 'sentence', 'paragraph', 'surroundtext', 'surroundtextsingle', 'teaser', 'array'
$search->setHighlightType('surroundtext');

// Ergebnis-Limit (Offset, Anzahl)
$search->setLimit(0, 20);

// Sortierung
$search->setOrder(['clang' => 'ASC']);
```

### Suchverhalten konfigurieren

```php
// UND/ODER-Verknüpfung: 'and', 'or'
$search->setLogicalMode('and');

// Textmodus: 'plain' (Plaintext), 'unmodified' (HTML), 'both'
$search->setTextMode('plain');

// MySQL-Modus: 'like' oder 'match'
$search->setSearchMode('like');

// Wörter extra gewichten
$search->addWhitelist(['wichtig' => 5, 'redaxo' => 3]);
```

### Indexierung

```php
$search->generateIndex();                           // Kompletter Neuaufbau
$search->indexArticle(int $id, int|false $clang);   // Einzelnen Artikel indexieren
$search->indexColumn(string $table, string $column); // DB-Spalte indexieren
$search->indexFile(string $filename);                // Datei indexieren
$search->deleteCache();                              // Such-Cache leeren
$search->deleteIndex();                              // Gesamten Index löschen
```

## Ergebnis-Array

```php
$result = $search->search('suchbegriff');
$result['count']       // int — Gesamtanzahl Treffer (ohne LIMIT)
$result['hits']        // array — Treffer-Array
$result['keywords']    // array — Gesuchte Begriffe mit Gewichtung
$result['searchterm']  // string — Originaler Suchbegriff
$result['time']        // float — Suchdauer in Sekunden
$result['simwords']    // array — Ähnliche Wörter (wenn aktiviert)
$result['simwordsnewsearch'] // string — Vorgeschlagene Suche mit ähnlichen Wörtern
$result['blacklisted'] // array|false — Gesperrte Wörter die gesucht wurden
```

### Einzelner Treffer (`$result['hits'][$i]`)

| Key | Typ | Beschreibung |
|---|---|---|
| `fid` | int/string | Fremd-ID (Artikel-ID, URL-Hash, DB-Primary-Key) |
| `type` | string | `'article'`, `'url'`, `'db_column'`, `'file'` |
| `table` | string | Quell-Tabelle (z.B. `rex_article`) |
| `column` | string/null | Quell-Spalte (nur bei `db_column`) |
| `clang` | int/null | Sprach-ID |
| `plaintext` | string | Indexierter Klartext |
| `highlightedtext` | string | Text mit hervorgehobenen Suchbegriffen |
| `teaser` | string | Kurztext des Treffers |
| `article_teaser` | string | Teaser aus dem Artikel-Content |
| `values` | array/null | Zusätzliche indexierte Spaltenwerte |
| `filename` | string/null | Dateiname (nur bei `file`) |
| `fileext` | string/null | Dateiendung (nur bei `file`) |

## Treffer-Typen unterscheiden

```php
foreach ($result['hits'] as $hit) {
    switch ($hit['type']) {
        case 'article':
            $url = rex_getUrl($hit['fid'], $hit['clang']);
            $name = rex_article::get($hit['fid'], $hit['clang'])?->getName();
            break;

        case 'db_column':
            // Treffer aus Datenbank-Spalte
            // $hit['table'], $hit['column'], $hit['fid'] nutzen
            if ($hit['table'] === rex::getTable('article')) {
                $url = rex_getUrl($hit['fid'], $hit['clang']);
            }
            break;

        case 'file':
            $url = rex_url::media($hit['filename']);
            break;

        case 'url':
            // URL-Addon Treffer
            // URL muss über URL-Addon-Profil rekonstruiert werden
            break;
    }
}
```

## Extension Points

### SEARCH_IT_INDEX_ARTICLE
Wird vor der Indexierung eines Artikels aufgerufen. `false` zurückgeben um Artikel auszuschließen.

```php
rex_extension::register('SEARCH_IT_INDEX_ARTICLE', function(rex_extension_point $ep) {
    $article = $ep->getParam('article');
    if ($article->getValue('art_noindex') == 1) {
        return false; // Artikel nicht indexieren
    }
});
```

### SEARCH_IT_PLAINTEXT
Plaintext-Umwandlung anpassen.

```php
rex_extension::register('SEARCH_IT_PLAINTEXT', function(rex_extension_point $ep) {
    $text = $ep->getSubject();
    $text = str_replace('Suchmaschine', '', $text);
    return $text;
    // Oder als Array: ['text' => $text, 'process' => true] (true = Standard-Plaintext danach noch ausführen)
});
```

### SEARCH_IT_SEARCH_EXECUTED
Nach jeder Suche — für Logging, Analytics, etc.

```php
rex_extension::register('SEARCH_IT_SEARCH_EXECUTED', function(rex_extension_point $ep) {
    $result = $ep->getSubject();
    // $result['searchterm'], $result['count'], etc.
});
```

## Console-Befehle

```bash
php redaxo/bin/console search_it:reindex      # Kompletten Index neu aufbauen
php redaxo/bin/console search_it:clearCache    # Such-Cache leeren
```

## Cronjobs

Im REDAXO-Backend unter Cronjob-Addon konfigurierbar:
- **Search it: Reindex** — Neuindexierung (komplett, nur Artikel, nur Spalten, nur URLs)
- **Search it: Cache löschen** — Cache leeren

## YRewrite Multi-Domain

Bei mehreren Domains mit YRewrite die Suche auf eine Domain beschränken:

```php
$search = new SearchIt();
// Mount-ID der Domain als Kategorie-Baum
$search->searchInCategoryTree(rex_yrewrite::getDomainByName('meinedomain.de')->getMountId());
$result = $search->search($searchTerm);
```

## Pagination

```php
$page = rex_request('page', 'int', 1);
$perPage = 10;

$search = new SearchIt();
$search->setLimit(($page - 1) * $perPage, $perPage);
$result = $search->search($searchTerm);

$totalPages = ceil($result['count'] / $perPage);
```

## Ähnlichkeitssuche (Did you mean?)

Muss in den Addon-Einstellungen aktiviert sein. Baut einen Keyword-Index aus erfolgreichen Suchen auf.

```php
$result = $search->search($searchTerm);

if ($result['count'] === 0 && !empty($result['simwordsnewsearch'])) {
    echo 'Meinten Sie: <a href="?search=' . urlencode($result['simwordsnewsearch']) . '">'
        . rex_escape($result['simwordsnewsearch']) . '</a>';
}
```

## Autocomplete / Suggest

Eingebaut als API-Endpoint. Im Frontend aktivieren:

1. Addon-Einstellungen → Suggest → aktivieren
2. Suchformular braucht `class="search_it-form"` und Input mit `name="search"`
3. Generierten JS-Code vor `</body>` einfügen (wird in den Einstellungen angezeigt)

## Namespace-Struktur (ab v7)

```
FriendsOfRedaxo\SearchIt\
├── SearchIt                    — Haupt-Klasse (Suche + Indexierung)
├── Api\Autocomplete            — Autocomplete API
├── Cache\SearchCache           — Such-Cache
├── Console\ReindexCommand      — Console: search_it:reindex
├── Console\ClearCacheCommand   — Console: search_it:clearCache
├── Cronjob\Reindex             — Cronjob: Neuindexierung
├── Cronjob\ClearCache          — Cronjob: Cache leeren
├── EventHandler                — REDAXO Extension Point Handler
├── Helper\ArticleHelper        — Artikel/Kategorie-Listen
├── Helper\ColognePhonetic      — Kölner Phonetik (Ähnlichkeitssuche)
├── Helper\FileHelper           — Datei-/Verzeichnis-Listen
├── Helper\FormBuilder          — Einstellungsformulare
├── Helper\SocketHelper         — HTTP Socket für Indexierung
├── Helper\UrlAddon             — URL-Addon Integration
├── Index\KeywordStore          — Keyword-Speicherung
├── Pdf\PdfConverter            — PDF-zu-Text Konvertierung
├── Plaintext\PlaintextConverter— HTML-zu-Plaintext
├── Search\Highlighter          — Frontend Search Highlighter
├── Stats\Statistics            — Suchstatistiken
```

Alte Klassennamen funktionieren weiterhin via `class_alias()` (deprecated).
