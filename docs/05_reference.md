# Klassen-Referenz

Namespace: `FriendsOfRedaxo\SearchIt\SearchIt`

```php
use FriendsOfRedaxo\SearchIt\SearchIt;
```

Der alte Klassenname `search_it` funktioniert weiterhin (deprecated).

## Indexierung

 Methode                                                                                                      | Erläuterung
--------------------------------------------------------------------------------------------------------------|----------------------------------------------------------------------------
 `__construct(int\|false $clang = false, bool $loadSettings = true, bool $useStopwords = true)`               | Konstruktor
 `generateIndex(): void`                                                                                       | Erstellt den kompletten Index neu.
 `indexArticle(int $id, int\|false $clang = false): int`                                                      | Indexiert einen bestimmten Artikel.
 `unindexArticle(int $id, int\|false $clang = false): void`                                                   | Entfernt einen Artikel aus dem Index.
 `indexColumn(string $table, string $column, string\|false $idcol = false, int\|false $id = false, ...): int\|false` | Indexiert eine bestimmte Datenbankspalte.
 `indexFile(string $filename, bool $doPlaintext = false, int\|false $clang = false, ...): int`                | Indexiert eine bestimmte Datei.
 `indexURL(int $id, int $article_id, int $clang_id, int $profile_id, int $data_id): int`                     | Indexiert eine URL aus dem URL-Addon.
 `unindexURL(int $id): void`                                                                                  | Entfernt eine URL aus dem Index.
 `deleteIndex(): void`                                                                                         | Löscht den kompletten Suchindex.
 `deleteIndexForType(string $texttype): void`                                                                  | Löscht den Index für einen bestimmten Texttyp.
 `deleteCache(): void`                                                                                         | Löscht den Such-Cache.
 `deleteKeywords(): void`                                                                                      | Löscht den Keyword-Index.

## Suche einschränken

 Methode                                                        | Erläuterung
----------------------------------------------------------------|------------------------------------------------------------------------------------------
 `searchInArticles(array $ids): void`                           | Setzt die IDs der Artikel, in denen gesucht werden soll.
 `searchInCategories(array $ids): void`                         | Setzt die IDs der Kategorien, in denen gesucht werden soll.
 `searchInCategoryTree(int $id): void`                          | Setzt eine Kategorie als Wurzel — alle Artikel müssen darin enthalten sein.
 `searchNotInCategories(array $ids): void`                      | Schließt die angegebenen Kategorien von den Suchergebnissen aus.
 `searchNotInCategoryTree(int $id): void`                       | Schließt eine Kategorie inkl. aller Unterkategorien von den Suchergebnissen aus.
 `searchInFileCategories(array\|bool $ids): void`               | Setzt die IDs der Medienpool-Kategorien, in denen gesucht werden soll.
 `searchInDbColumn(string $table, string $column): void`        | Beschränkt die Suche auf bestimmte Datenbankspalten.
 `setSearchAllArticlesAnyway(bool $bool = false): void`         | Legt fest, ob Artikel durchsucht werden sollen.
 `setWhere(string $where): void`                                | Setzt eine zusätzliche WHERE-Klausel.
 `setOrder(array $order, bool $put_first = false): void`        | Setzt die Sortierung der Ergebnisse.
 `doGroupBy(bool $bool = true): void`                           | Gruppiert Ergebnisse nach ftable, fid, clang.

## Suchverhalten konfigurieren

 Methode                                                        | Erläuterung
----------------------------------------------------------------|------------------------------------------------------------------------------------------
 `setLogicalMode(string $logicalMode): void`                    | Logische Verknüpfung: `'and'` oder `'or'`.
 `setTextMode(string $textMode): void`                          | Textmodus: `'plain'`, `'unmodified'` oder `'both'`.
 `setSearchMode(string $searchMode): void`                      | MySQL-Modus: `'like'` oder `'match'`.
 `addWhitelist(array $whitelist): void`                         | Gewichtet bestimmte Wörter höher.

## Ergebnis-Darstellung

 Methode                                                        | Erläuterung
----------------------------------------------------------------|------------------------------------------------------------------------------------------
 `setSurroundTags(string\|array $tags, string\|false $endtag = false): void` | Setzt die Tags um gefundene Suchbegriffe.
 `setLimit(int\|array $limit, int\|false $countLimit = false): void` | Setzt das Maximum an Ergebnissen (Offset, Anzahl).
 `setMaxTeaserChars(int $count): void`                          | Setzt die maximale Zeichenanzahl für den Teaser.
 `setMaxHighlightedTextChars(int $count): void`                 | Setzt die maximale Zeichenanzahl um gefundene Suchbegriffe.
 `setHighlightType(string $type): void`                         | Hervorhebungstyp: `'sentence'`, `'paragraph'`, `'surroundtext'`, `'surroundtextsingle'`, `'teaser'`, `'array'`.
 `setBlacklist(array $words): void`                             | Setzt Wörter, die nicht gefunden werden dürfen.
 `setCaseInsensitive(bool $ci = true): void`                    | Groß-/Kleinschreibung ignorieren.

## Suche ausführen

 Methode                                                        | Erläuterung
----------------------------------------------------------------|------------------------------------------------------------------------------------------
 `search(string $search): array`                                | Führt die Suche aus und gibt das Ergebnis-Array zurück.
 `setSearchString(string $searchString): void`                  | Setzt den Suchbegriff.
 `parseSearchString(string $searchString): array`               | Konvertiert den Suchstring in ein Array.
 `getHighlightedText(string $text): string\|array`              | Hebt gefundene Suchbegriffe im Text hervor.
