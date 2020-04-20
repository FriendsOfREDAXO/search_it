# Indexierung

`Search it` erstellt den Index, in dem es die Artikel der Website im Frontend aufruft und den Artikelinhalt indexiert.
D.h. im ersten Schritt werden nur im Frontend sichtbare Inhalte gefunden. Insbesondere werden also Passwort-geschützte Inhalte nicht im Index landen.

Über die Auswahl von Datenbanktabellenspalten im Register "Zusätzl. Datenquellen" können auch nicht im Frontend sichtbare Inhalte indexiert werden.

> **Hinweis:** Voraussetzung für die Artikelindexierung ist, dass die Artikel im Frontend erreichbar sind und bspw. nicht durch Addons oder aus anderen Gründen der Aufruf der Seite für nicht eingeloggte Nutzer blockiert wird. Addons wie bspw. `maintenance` oder ein ungültiges SSL-Zertifikat können die Indexierung blockieren.

## Automatisch indexieren / Index erneuern

Eine Automatisch De-(Indexierung) erfolgt im Moment mit folgenden Extension-Points:

Extension Point | Erläuterung
------ | ------
ART_DELETED|Wenn ein Artikel gelöscht wird, wird er aus dem Suchcache entfernt.
ART_META_UPDATED, ART_ADDED, CAT_UPDATED, ART_UPDATED|Wenn Metainfos geändert wurden, werden alle ausgewählten DB-Spalten aus der Tabelle rex_article neu indexiert.
ART_STATUS| Ein Artikel, der offline geschaltet wird, wird deindexiert, bei online indexiert.
CAT_DELETED| Ausgabe einer Meldung, dass der Index erneuert werden muss.
CAT_STATUS, CAT_ADDED| Eine Kategorie, die offline geschaltet wird, wird deindexiert, bei online indexiert.
MEDIA_ADDED, MEDIA_UPDATED|Wenn ein Medium hinzugefügt wurde, werden alle ausgewählten DB-Spalten aus der Tabelle rex_file neu indexiert.
SLICE_ADDED, SLICE_DELETED, SLICE_UPDATED|Der Artikel wird neu indexiert


### Reindexierung von Artikeln via Cronjob

`Search it` fügt ein eigenes Cronjob-Profil hinzu, das sich im Cronjob-AddOn zeitgesteuert ausführen lässt. Um diese Funktion zu nutzen, muss ein neuer Cronjob des Typs `Search it: Reindexieren` ausgewählt werden.

Um URLs des URL-Addons automatisch neu zu indexieren, muss der Cronjob erstellt sein, da aktuell keine Extension Points existieren.

### Reindexierung von URLs aus dem URL-Addon

Die Klasse `search_it` bietet die Methode `indexURL` an. Über diese Methode können URLs neu oder wieder indexiert werden. Außerdem bietet sie die Methode `unindexURL` an. Über diese Methode können URLs aus dem Index entfernt werden.

Nachfolgend ein Beispiel, um den kompletten URL-Index neu aufzubauen:

```php
$url_sql = rex_sql::factory();
$url_sql->setTable(search_it_getUrlAddOnTableName());
if ($url_sql->select('id, article_id, clang_id, profile_id, data_id')) {
	// index und cache zuerst löschen, damit keine alten Einträge überleben
	$search_it->deleteIndexForType("url");
    // index neu aufbauen
	foreach ($url_sql->getArray() as $url) {
		$search_it->indexUrl($url['id'], $url['article_id'], $url['clang_id'], $url['profile_id'], $url['data_id']);
	}
}
```

### Reindexierung von Datenbank-Feldern

Die Klasse `search_it` bietet allerdings die Methode `indexColumn` an. Über diese Methode können Datenbankspalten neu oder wieder indexiert werden. Müssen die Datenbankspalten nur zu einem bestimmten Datensatz indexiert werden, kann außerdem die ID dieses Datensatzes angegeben werden. Search it wird dann auch nur den betroffenen Datensatz reindexieren.

### Alles reindexieren

```php
    $search_it = new search_it;
    $includeColumns = is_array(rex_addon::get('search_it')->getConfig('include')) ? rex_addon::get('search_it')->getConfig('include') : array();

    foreach( $includeColumns as $table => $columnArray ){
        foreach( $columnArray as $column ){
            $search_it->indexColumn($table, $column);
        }
    }
```

### Für AddOns

Ein AddOn arbeitet mit einer eigenen Datenbank-Tabelle, hier: `table`. Search it soll Inhalte dieses AddOns auch automatisch reindexieren. Da das AddOn selbst weiß, wann die Beispieldatenbank-Feld `field` reindexiert werden soll, kann die Methode `indexColumn` von diesem AddOn aufgerufen werden:

```php
$search_it = new search_it;
$search_it->indexColumn('table', 'field'[, 'id'[, $datensatz_id]]);
```

Die Methode `indexColumn` benötigt daher folgende Parameter:

* Die Namen der Datenbanktabelle (hier: `table`) und
* das Datenbank-Feld (hier: `field`),
* optional der Primärschlüssel (Standard: `id`) und
* optional die ID des Datensatzes, der reindexiert wird (Standard: alle).

## Tipps zur Konfiguration

### Was soll durchsuchbar sein? (Artikel, Meta-Infos, Medien, Datenbanktabellen)

Standardmäßig ist Search it eine reine Volltextsuche. Begriffe, die in den Suchergebnissen gefunden werden sollen, müssen demnach immer innerhalb eines Artikels ausgegeben werden. Meta-Informationen, darunter der Artikelinhalt, sowie Datenbanktabellen und Medien werden nicht durchsucht.

#### Artikelnamen und weitere Meta-Infos indexieren

1. Unter `Search it` > `Einstellungen` > `Zusätzliche Datenquellen` > `rex_article` die gewünschten Datenbankfelder anhaken.
2. Das Suchausgabe-Modul muss den Treffer innerhalb der `rex_article`-Tabelle abfangen: `if($hit['type'] == 'db_column' AND $hit['table'] == rex::getTablePrefix().'article')`

> Tipp: mit `dump($hit)` lassen sich weitere Informationen zum passenden Treffer einsehen, bspw. `$hit['clang']` für die Sprach-ID des Treffers

#### Datenbank-Tabellen indexieren

1. Unter `Search it` > `Einstellungen` > `Zusätzliche Datenquellen` > die gewünschten Datenbankfelder der Tabelle anhaken, z.B. `rex_meine_tabelle`
2. Das Suchausgabe-Modul muss den Treffer innerhalb der `rex_article`-Tabelle abfangen: `if($hit['type'] == 'db_column' AND $hit['table'] == rex_meine_tabelle')`

> Tipp: mit `dump($hit)` lassen sich weitere Informationen zum passenden Treffer ausgeben, bspw. `$hit['column']` für das Feld, in dem der Treffer ausgelöst wurde, oder `$hit['fid']` für die ID des Datensatzes.

> Tipp mit einer `VIEW` lassen sich die zu indexierenden Datensätze im Vorfeld filtern. [Weitere Informationen zu Views](https://de.wikibooks.org/wiki/Einf%C3%BChrung_in_SQL:_Erstellen_von_Views)

### Lassen sich verschiedene Suchergebnisse realisieren?

Ja! Es können unterschiedliche Sucheingabe- und Suchausgabe-Module erstellt werden.

Zum Beispiel könnte eine Seite 2 Suchen haben: Eine Suche, die nur Artikelinhalte als Suchergebnis ausgibt - und eine Suche, die nur Dokumente im Medienpool durchsucht. Oder eine Suche, die nur in Kategorie A sucht - und eine Suchergebnis-Seite, die nur in Kategorie B sucht.

Dazu werden in der Suchmodul-Ausgabe zusätzliche Parameter vor dem Aufruf von `search()` übergeben. Beispiele:

```php
$search_it = new search_it(REX_CLANG_ID); // Nur in einer bestimmten Kategorie suchen

# Artikel- / Struktur-Suche
$search_it->searchInCategories(array(5,6,13)); // durchsucht nur die Kategorien 5, 6 und 13, oder
$search_it->setSearchAllArticlesAnyway(false) // Keine Artikel durchsuchen
```

```php
# Datenbank-Suche
$search_it->searchInDbColumn('rex_article', 'name'); // Durchsucht das Meta-Info-Feld "name" (dieses muss in den Search it-Einstellungen unter "Zusätzliche Datenquellen" markiert sein!)
$search_it->searchInDbColumn('rex_meine_tabelle', 'mein_feld'); // Durchsucht das Feld "mein_feld" (dieses muss in den Search it-Einstellungen unter "Zusätzliche Datenquellen" markiert sein!)
```

```php
# Datei-Suche
$rexsearch_article->searchInFileCategories(false); // durchsucht keine Dateien
$rexsearch_article->searchInFileCategories(true); // durchsucht Dateien
```

```php
# im Medienpool in Kategorie 5 nur pdf durchsuchen
$search_it->searchInFileCategories(5);
$search_it->setWhere('fileext = "pdf"');
# im Dateisystem-Ordner /example/ alle Dateien durchsuchen
$search_it->setWhere('filename LIKE 'example/%');
```

```php
$result = $search_it->search(rex_request('search', 'string')); // Suche ausführen.
```


> Weitere Tipps und Tricks zum Filtern von Suchergebnissen in den FAQ.
