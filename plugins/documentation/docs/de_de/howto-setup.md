# Tipps zur Konfiguration

## Was soll durchsuchbar sein? (Artikel, Meta-Infos, Medien, Datenbanktabellen) 

Standardmäßig ist Search it eine reine Volltextsuche. Begriffe, die in den Suchergebnissen gefunden werden sollen, müssen demnach immer innerhalb eines Artikels ausgegeben werden. Meta-Informationen, darunter der Artikelinhalt, sowie Datenbanktabellen und Medien werden nicht durchsucht.

### Artikelnamen und weitere Meta-Infos indexieren

1. Unter `Search it` > `Einstellungen` > `Zusätzliche Datenquellen` > `rex_article` die gewünschten Datenbankfelder anhaken.
2. Das Suchausgabe-Modul muss den Treffer innerhalb der `rex_article`-Tabelle abfangen: `if($hit['type'] == 'db_column' AND $hit['table'] == rex::getTablePrefix().'article')`

> Tipp: mit `dump($hit)` lassen sich weitere Informationen zum passenden Treffer einsehen, bspw. `$hit['clang']` für die Sprach-ID des Treffers
 
### Datenbank-Tabellen indexieren

1. Unter `Search it` > `Einstellungen` > `Zusätzliche Datenquellen` > die gewünschten Datenbankfelder der Tabelle anhaken, z.B. `rex_meine_tabelle`
2. Das Suchausgabe-Modul muss den Treffer innerhalb der `rex_article`-Tabelle abfangen: `if($hit['type'] == 'db_column' AND $hit['table'] == rex_meine_tabelle')`

> Tipp: mit `dump($hit)` lassen sich weitere Informationen zum passenden Treffer ausgeben, bspw. `$hit['column']` für das Feld, in dem der Treffer ausgelöst wurde, oder `$hit['fid']` für die ID des Datensatzes.

> Tipp mit einer `VIEW` lassen sich die zu indexierenden Datensätze im Vorfeld filtern. (Weitere Informationen zu Views)[https://de.wikibooks.org/wiki/Einf%C3%BChrung_in_SQL:_Erstellen_von_Views]

## Lassen sich verschiedene Suchergebnisse realisieren?

Ja! Es können unterschiedliche Sucheingabe- und Suchausgabe-Module erstellt werden. 

Zum Beispiel könnte eine Seite 2 Suchen haben: Eine Suche, die nur Artikelinhalte als Suchergebnis ausgibt - und eine Suche, die nur Dokumente im Medienpool durchsucht. Oder eine Suche, die nur in Kategorie A sucht - und eine Suchergebnis-Seite, die nur in Kategorie B sucht. 

Dazu werden in der Suchmodul-Ausgabe zusätzliche Parameter vor dem Aufruf von `search()` übergeben. Beispiele:

```
$search_it = new search_it(REX_CLANG_ID); // Nur in einer bestimmten Kategorie suchen

# $rexsearch_article->setLimit(array($offset, $max));

# Artikel- / Struktur-Suche
# $search_it->searchInCategories(array(5,6,13)); // durchsucht nur die Kategorien 5, 6 und 13
# $search_it->setSearchAllArticlesAnyway(false) // Keine Artikel durchsuchen

# Datenbank-Suche 
# $search_it->searchInDbColumn('rex_article', 'name'); // Durchsucht das Meta-Info-Feld "name" (dieses muss in den Search it-Einstellungen unter "Zusätzliche Datenquellen" markiert sein!)
# $search_it->searchInDbColumn('rex_meine_tabelle', 'mein_feld'); // Durchsucht das Feld "mein_feld" (dieses muss in den Search it-Einstellungen unter "Zusätzliche Datenquellen" markiert sein!) 

# Datei-Suche
# $rexsearch_article->searchInFileCategories(false); // durchsucht keine Dateien
# $rexsearch_article->searchInFileCategories(true); // durchsucht Dateien

$result = $search_it->search(rex_request('search', 'string')); // Suche ausführen.
```

## Filtern und Sortieren von Suchergebnissen

Das Sortieren von Suchergebnissen ist derzeit noch nicht möglich. Ein passender PR auf GitHub ist jedoch willkommen! Allerdings lassen sich mit ein paar Kniffen dennoch bestimmte Inhalte und Daten von der Indexierung ausschließen.

> Hinweis: Die hier gezeigten Beispiele gelten nur für Search it und werden nicht von Suchmaschinen, bspw. Google, berücksichtigt.

### Datensätze in Datenbanktabellen filtern
 
Mit einer `VIEW` lassen sich die zu indexierenden Datensätze bereits im Vorfeld filtern:

1. Eine VIEW in der Datenbanktabelle erstellen (Weitere Informationen zu Views)[https://de.wikibooks.org/wiki/Einf%C3%BChrung_in_SQL:_Erstellen_von_Views]
2. In den Search it-Einstellungen statt der Datenbanktabelle die gewünschte View indexieren.

### Module, Blöcke, Artikel oder bestimmte Abschnitte filtern

Das Plaintext-Plugin hat die Möglichkeit, anhand bestimmter Selektoren Inhalte auszuschließen. So werden bspw. `<header>` oder `<footer>` nicht indexiert. Mit diesem Trick können auch ganze Module von der Suche ausschließen. 

**Beispiel-Modulausgabe:**

```
<section class="donotsearch">REX_VALUE[1]</section>
```

** Beispiel Plaintext-Selektor:** `section.donotsearch`

Auf dieselbe Weise lassen sich auch Artikel oder Kategorien von der Indexierung ausschließen, indem eine passende Klasse dem `<body>`-Tag zugewiesen wird.

