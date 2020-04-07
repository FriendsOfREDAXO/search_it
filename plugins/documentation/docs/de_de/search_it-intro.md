# Über Search it

`Search it` ist ein REDAXO 5-AddOn für eine Volltextsuche im Frontend.

Dabei werden Artikel, Medien, Dateien, PDF-Inhalte und Datenbank-Felder in einer DB-Tabelle des AddOns gespeichert und ausgewertet. Suchanfragen können außerdem in einer Cache-Tabelle gespeichert werden. Das spart Serverrechenleistung und führt zur schnelleren Anzeige von Suchergebnissen.

`Search it` basiert auf [RexSearch (Xong) für REDAXO 4](https://github.com/xong/rexsearch/).

## Systemvoraussetzungen

* `PHP ^7.0`
* `REDAXO >= 5.5.0`
* `pdftotext` [optional für das Durchsuchen von pdf-Inhalten, [Link](https://www.xpdfreader.com/pdftotext-man.html)]


## Plugins

* `Plaintext`: Reduziert Artikel auf reinen Text und entfernt dabei alle HTML-Tags. 
* `Statistik`: Liefert Informationen zur `Search it`-Datenbank und zu den häufigsten Suchanfragen.
* `Dokumentation`: Zeigt diese Dokumentation an.

> Hinweis: Die Plugins `Reindex` und `Search Highlighter` aus `RexSearch für REDAXO 4` wurden in `Seach it` integriert.

## Erste Schritte

* Installation des aktuellen Release über GitHub oder den REDAXO-Installer
* `Search it`-AddOn und Plugins aktivieren
* Einstellungen von `Search it` festlegen [(Hilfe)](search_it-settings.md)
* Indexierung starten
* Suchergebnis-Artikel anlegen 
* Suchfeld-Modul / Suchfeld-Template hinzufügen [(Hilfe)](module-form.md)
* Suchergebnis-Modul hinzufügen [(Hilfe)](module-simple.md)

## Häufige Fehler

* bleibt die Indextabelle leer könnte ein .htaccess Zugriffsschutz die Indexierung verhindern
* bleibt die Indextabelle leer, ist eventuell ein "Minifier" im Einsatz, der HTML-Kommentare aus dem Quellcode entfernt.
Die braucht `Search it` (damit werden die zu indexierenden Inhalte markiert). Man kann auf den URL-Parameter 'search_it_build_index' prüfen, z.B. durch `rex_request('search_it_build_index', 'int', false)` - wenn er gesetzt ist, ist es ein Aufruf von `Search it`
* Findet sich im syslog die Meldung `Warning: You should not use non-secure socket connections while connecting to "my-domain.tld" ` so liegt dies daran, das die eigene Domain in den Einstellungen unter System ( oder bei Verwendung von des Addons `YRewrite` in den Einstellungen dort) ohne `https://` eingetragen wurde.

## Wo finde ich weitere Hilfe?

Die aktuelle Search it-Version wird in [FriendsOfREDAXO](https://github.com/friendsofredaxo/search_it) gepflegt. Dort können Fragen gestellt und Bugs gemeldet werden (Issues). Fragen können auch im [REDAXO-Forum](www.redaxo.org/de/forum/) oder im [REDAXO-Channel auf Slack](https://friendsofredaxo.slack.com/messages/redaxo/) gestellt werden.

## Hinweis zur Installation

Die Installation erfolgt über den REDAXO 5 Installer, alternativ gibt es die aktuellste Beta-Version auf [GitHub](https://github.com/friendsofredaxo/search_it). 

Bei der Installation werden fünf Datenbanktabellen angelegt: 
* `rex_tmp_search_it_index` für die Indexierung von Artikeln und DB-Spalten
* `rex_tmp_search_it_keywords` für die Ähnlichkeitssuche
* `rex_tmp_search_it_cache` und `rex_tmp_search_it_cacheindex_ids` für den Suchcache
* `rex_tmp_search_it_stats_searchterms` für die Statistik
