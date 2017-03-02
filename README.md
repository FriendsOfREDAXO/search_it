# REDAXO 5 AddOn Search it

Search it ist ein REDAXO 5-AddOn für eine Volltextsuche im Frontend.

Dabei werden Artikel, Medien, Dateien, PDF-Inhalte und Datenbank-Felder in einer DB-Tabelle des AddOns gespeichert und ausgewertet. Suchanfragen können außerdem in einer Cache-Tabelle gespeichert werden. Das spart Serverrechenleistung und führt zur schnelleren Anzeige von Suchergebnissen.

Search it basiert auf [RexSearch (Xong) für REDAXO 4](https://github.com/xong/rexsearch/).

## Systemvoraussetzungen

* `PHP >= 5.5`
* `MySQL >= 5.1`
* `REDAXO >= 5.2`

## Plugins

* `Statistik`: Liefert Informationen zur Search it-Datenbank und zu den häufigsten Suchanfragen.
* `Dokumentation`: Zeigt diese Dokumentation an.
* `Plaintext`: Erlaubt es zu bestimmen, was in den Index aufgenommen wird

> Hinweis: Die Plugins `Reindex` und `Search Highlighter` aus RexSearch für REDAXO 4 wurden in Seach it integriert.

## Wo finde ich weitere Hilfe?

Search it verfügt über ein umfangreiches Dokumentations-Plugin im Backend, das auch Beispiel Module für die Suche enthält.
Fragen können auch im [REDAXO-Forum](www.redaxo.org/de/forum/) oder im [REDAXO-Channel auf Slack](https://friendsofredaxo.slack.com/messages/redaxo/) gestellt werden.

# Installation

Die Installation erfolgt über den REDAXO-Installer, alternativ gibt es die aktuellste Beta-Version auf [GitHub](https://github.com/FriendsOfREDAXO/search_it). 

Bei der Installation werden fünf Datenbanktabellen angelegt: 
* `rex_search_it_index` für die Indexierung von Artikeln und DB-Spalten
* `rex_search_it_keywords` für die Ähnlichkeitssuche
* `rex_search_it_cache` und `rex_search_it_cacheindex_ids` für den Suchcache
* `rex_search_it_stats_searchterms` für die Statistik

## First Steps

Nach der Installation sollten zunächst die Einstellungen vorgenommen werden und anschließend der Index vollständig generiert werden.
