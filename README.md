# REDAXO 5 AddOn Search it

Search it ist ein REDAXO 5-AddOn für eine Volltextsuche im Frontend.

Dabei werden Artikel, Medien, Dateien, PDF-Inhalte und Datenbank-Felder in einer DB-Tabelle des AddOns gespeichert und ausgewertet. Suchanfragen können außerdem in einer Cache-Tabelle gespeichert werden. Das spart Serverrechenleistung und führt zur schnelleren Anzeige von Suchergebnissen.

## Systemvoraussetzungen

* `PHP >= 7.0`
* `REDAXO >= 5.5`
* `pdftotext` [optional für das Durchsuchen von pdf-Inhalten, [Link](https://www.xpdfreader.com/pdftotext-man.html)]

## Plugins

* `Statistik`: Liefert Informationen zur Search it-Datenbank und zu den häufigsten Suchanfragen.
* `Dokumentation`: Zeigt diese Dokumentation an.
* `Plaintext`: Erlaubt es zu bestimmen, was in den Index aufgenommen wird

> Hinweis: Die Plugins `Reindex` und `Search Highlighter` aus RexSearch für REDAXO 4 wurden in Seach it integriert.

## Wo finde ich weitere Hilfe?

Search it verfügt über ein umfangreiches Dokumentations-Plugin im Backend, das auch Beispiel Module für die Suche enthält.
Fragen können auch im [REDAXO-Channel auf Slack](https://friendsofredaxo.slack.com/messages/redaxo/) gestellt werden.

# Installation

Die Installation erfolgt über den REDAXO-Installer, alternativ gibt es die aktuellste Beta-Version auf [GitHub](https://github.com/FriendsOfREDAXO/search_it).

Bei der Installation werden fünf Datenbanktabellen angelegt:
* `rex_tmp_search_it_index` für die Indexierung von Artikeln und DB-Spalten
* `rex_tmp_search_it_keywords` für die Ähnlichkeitssuche
* `rex_tmp_search_it_cache` und `rex_tmp_search_it_cacheindex_ids` für den Suchcache
* `rex_tmp_search_it_stats_searchterms` für die Statistik

Die Tabellen werden nicht in die Datenbank-Sicherung des backup-Addons miteinbezogen.

## First Steps

Nach der Installation sollten zunächst die Einstellungen vorgenommen werden und anschließend der Index vollständig generiert werden.

## Lizenz
MIT Lizenz, siehe [LICENSE.md](https://github.com/FriendsOfREDAXO/search_it/blob/master/LICENSE.md) 

## Autor
**Friends Of REDAXO** 
http://www.redaxo.org 
https://github.com/FriendsOfREDAXO 
**Projekt-Lead** 
[Norbert Micheel](https://github.com/tyrant88)

## Credits
Search it basiert auf: [RexSearch (Xong) für REDAXO 4](https://github.com/xong/rexsearch) 
[Norbert Micheel](https://github.com/tyrant88/) Portierung für R5 und aktiven Entwicklung
[Alexander Walther](https://github.com/skerbis) Dokumentation und Hilfe 
[Tobias Krais](https://github.com/tobiaskrais) URL Addon (>= 2.0) Support
[und weitere Entwickler...](https://github.com/FriendsOfREDAXO/search_it/graphs/contributors)
