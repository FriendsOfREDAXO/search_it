# REDAXO 5 AddOn Search it

Search it ist ein REDAXO 5-AddOn für eine Volltextsuche im Frontend.

Dabei werden Artikel, Medien, Dateien, PDF-Inhalte und Datenbank-Felder in einer
DB-Tabelle des AddOns gespeichert und ausgewertet. Suchanfragen können außerdem
in einer Cache-Tabelle gespeichert werden. Das spart Serverrechenleistung und
führt zur schnelleren Anzeige von Suchergebnissen.

## Systemvoraussetzungen

* `PHP >= 7.0`
* `REDAXO >= 5.6`
* [`pdftotext`](https://www.xpdfreader.com/pdftotext-man.html) für das
  Durchsuchen von pdf-Inhalten, optional.

## Plugins

> Hinweis: Die Plugins `Plaintext`, `Statistik`, `Reindex` und `Search Highlighter` aus RexSearch für
> REDAXO 4 wurden in Seach it integriert. Auch das PlugIn `Autocomplete` wurde integriert.

## Wo finde ich weitere Hilfe?

Search it verfügt über ein umfangreiches Dokumentations-Plugin im Backend, das
auch Beispiel Module für die Suche enthält.
Fragen können auch
im [REDAXO-Channel auf Slack](https://friendsofredaxo.slack.com/messages/redaxo/)
gestellt werden.

# Installation

Die Installation erfolgt über den REDAXO-Installer, alternativ gibt es die
aktuellste Beta-Version
auf [GitHub](https://github.com/FriendsOfREDAXO/search_it).

Bei der Installation werden fünf Datenbanktabellen angelegt:

* `rex_tmp_search_it_index` für die Indexierung von Artikeln und DB-Spalten
* `rex_tmp_search_it_keywords` für die Ähnlichkeitssuche
* `rex_tmp_search_it_cache` und `rex_tmp_search_it_cacheindex_ids` für den
  Suchcache
* `rex_tmp_search_it_stats_searchterms` für die Statistik

Die Tabellen werden nicht in die Datenbank-Sicherung des backup-Addons
miteinbezogen.

## First Steps

Nach der Installation sollten zunächst die Einstellungen vorgenommen werden und
anschließend der Index vollständig generiert werden.

## Lizenz

[MIT Lizenz](https://github.com/FriendsOfREDAXO/search_it/blob/master/LICENSE)

## Autor
**Friends Of REDAXO**
[Friends Of REDAXO](https://github.com/FriendsOfREDAXO)

## Credits

**Projekt-Lead**
[Norbert Micheel](https://github.com/tyrant88)

Search it basiert
auf: [RexSearch (Xong) für REDAXO 4](https://github.com/xong/rexsearch)   
[Norbert Micheel](https://github.com/tyrant88/) Portierung für R5 und aktiven
Entwicklung   
[Alexander Walther](https://github.com/skerbis) Dokumentation und Hilfe   
[Tobias Krais](https://github.com/tobiaskrais) URL Addon (>= 2.0) Support    
[und weitere Entwickler...](https://github.com/FriendsOfREDAXO/search_it/graphs/contributors)
