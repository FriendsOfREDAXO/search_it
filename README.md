# Redaxo 5 Addon Search it

Search it ist ein Redaxo 5-Addon für eine Volltextsuche im Frontend.

Dabei werden Artikel, Medien, Dateien, PDF-Inhalte und Datenbank-Felder in einer DB-Tabelle des Addons gespeichert und ausgewertet. Suchanfragen können außerdem in einer Cache-Tabelle gespeichert werden. Das spart Serverrechenleistung und führt zur schnelleren Anzeige von Suchergebnissen.

Search it basiert auf [RexSearch (Xong) für Redaxo 4](https://github.com/xong/rexsearch/).

## Systemvoraussetzungen

* `PHP >= 5.5`
* `MySQL >= 5.1`
* `Redaxo >= 5.2`

## Plugins

* `Statistik`: Liefert Informationen zur Search it-Datenbank und zu den häufigsten Suchanfragen.
* `Dokumentation`: Zeigt diese Dokumentation an.

> Hinweis: Die Plugins `Reindex` und `Search Highlighter` aus RexSearch für Redaxo 4 wurden in Seach it integriert.

## Wo finde ich weitere Hilfe?

Die aktuelle Search it-Version wird in [GitHub von tyrant88](https://github.com/tyrant88/search_it) gepflegt. Dort können Fragen gestellt und Bugs gemeldet werden (Issues). Fragen können auch im [Redaxo Forum](www.redaxo.org/de/forum/) oder im [Redaxo-Channel auf Slack](https://friendsofredaxo.slack.com/messages/redaxo/) gestellt werden.

# Installation

Die Installation erfolgt über den Redaxo 5 Installer, alternativ gibt es die aktuellste Beta-Version auf [GitHub](https://github.com/tyrant88/search_it). 

Bei der Installation werden fünf Datenbanktabellen angelegt: 
* `rex_search_it_index` für die Indexierung von Artikeln und DB-Spalten
* `rex_search_it_keywords` für die Ähnlichkeitssuche
* `rex_search_it_cache` und `rex_search_it_cacheindex_ids` für den Suchcache
* `rex_search_it_stats_searchterms` für die Statistik

## First Steps

Nach der Installation sollten zunächst die Einstellungen vorgenommen werden und anschließend der Index vollständig generiert werden.

