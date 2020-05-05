# Änderungen von search_it

## Version 6.7.2 (2020-05-05)
- Fix stats plugin 2

## Version 6.7.1 (2020-05-02)
- Fix stats plugin - kein tmp Präfix nutzen #279

## Version 6.7.0 (2020-04-26)
- URLs aus dem URL Addon (>= 2.0) können indexiert werden. @TobiasKrais
- showTables is deprecated --> getTablesAndViews
- Die Tabellen sind nicht automatisch im Backup enthalten (mit "tmp_"-Präfix versehen) thx @alexplus
- bei schrittweiser Indexierung sind die Artikel in der Ausgabe verlinkt @alexplusde
- minimale PHP Version ergänzt @staabm
- install.sql -> install.php
- wird eine Tabelle oder Spalte gelöscht, die in "zusätzliche Datenquelle" angegeben ist und neuindexiert -> woops #222 thx @alexplusde
- autocomplete: keine Voreinstellung für 'similarwordsmode' erzeugte Fehler #219 thx @danielellm
- u.U. nichts indexiert, wenn REX_ARTICLE[] mehrfach verwendet wurde #138 thx @IngoWinter
- In Blacklist sind auch offline Kategorien auswählbar #215 thx @skerbis

## Version 6.6.6 (2019-08-14)
- Verbesserte, übersetzte Fehlermeldungen, korrekte Verwendung des jeweiligen Protokolls bei Weiterleitungen
- FIX: DB Suchindex unvollständig bei schrittweiser Indexierung #208 thx @dtpop
- Fix EP CAT_STATUS deindexiert nicht Kategorie-Startartikel #202 thx @alexwenz
- Fixed "non-numeric value encountered" warning #198 thx @staabm

## Version 6.6.5 (2019-03-04)
- Fix schrittweise Indexierung
- Fix Testsuche #192 @Pixeldaniel

## Version 6.6.4 (2019-02-25)
- Reindexierung überarbeitet, zusätzlicher EP "MEDIA_DELETED"
- highlighter fix #186 thx @alexwenz
- rex_escape statt htmlspecialchars
- Versuch fix PHP 7.3 @rolandsee
- pdf2txt fix for PHP 7.2 @olien
- fix verschachtelte search it tags
- Doku @thielpeter, @alexplusde

## Version 6.6.3 (2018-06-25)
- Fehler in Fehlermeldung bzgl Socketfehler
- Versionsnummer Autocomplete Plugin

## Version 6.6.2 (2018-01-17)
- Bugfix css Datei

## Version 6.6.1 (2018-01-17)
- Aufruf von pdftotext geändert, damit er auf mehr Servern funktioniert thx @helpy
- Autocomplete als neues Plugin thx! ! ! @rkemmere ! ! !
- Fehler SEARCH_IT_ART_IDNOTFOUND wurde gar nicht mehr ausgegeben
- Eine eigene Meldung für 404 und Endlos-Redirect
- Code für Meldungen verbessert / bei den Fehlern wurde nicht die Sparache angezeigt

## Version 6.5.2 (2018-01-08)
- Einige Meldungen im Backend enthielten nach Update auf REX 5.5 HTML Code ( thx helpy (Forum), #142 )

## Version 6.5.1 (2018-01-05)
- Schutz des Passwortfeldes im Backend (thx @tbaddade)
- Überarbeitung/Übersetzungen der Meldungen bei schrittweiser Indexierung ( #137 )

## Version 6.5.0 (2017-09-24)
- Eingabe eines Basic Auth Login ermöglicht Indexierung trotz '.htaccess'-Schutz #100 ( thx @Hirbod )
- Verbesserte Fehlermeldungen beim Indexieren per Backend

## Version 6.4.2 (2017-09-24)
- Error: string als array thx @tbaddade
- Verbesserung an der Doku
- DB-Spalten werden alphabetisch sortiert, nur noch eine Spalte @tbaddade
- beim Erstellen des Standard-Plaintext werden HTML Entities decodiert, damit kein ; eine Trennung erzeugt
    (Haupsächlich wegen &shy; vom Hypenator-PlugIn, #130, thx @greatif )
- Test erzeugt keine Änderung bei Statistik und der Test wird auch nicht gecached #122 ( thx @greatif )

## Version 6.4.1 (2017-09-18)
 - drei Beispielmodule waren anfällig für XSS
 - im ersten Fall (Suchergebnisse als Paragraph darstellen) ist das Problem mit den diakritsichen Zeichen #106 gelöst

## Version 6.4.0 (2017-09-11)
  - Test-Funktion im Backend @alexplusde

## Version 6.3.1 (2017-09-08)
  - Spalten Checkboxen nur noch in 2 Spalten #115 ( @tbaddade )
  - css und js werden nur noch auf den eigenen Backendseiten geladen #118 ( @olien )
  - extension point "YFORM_DATA_DELETED" wurde nicht beachtet
  - search_highlighter lieferte den < body> Tag nicht mit zurück #113 ( thx frood )
  - doppelte slashes in scanurl entfernt #111 ( thx Gerry, @skerbis )
  - yrewrite support added #105 (thx @palber )

## Version 6.3.0 (2017-03-23)
  - Indexmodi alle entfernt. Es wird nur noch Frontend indexiert.
  - die Tags werden nur noch bei der Indexierung eingefügt

## Version 6.2.0 (2017-03-22)
  - PlugIn Permissions @DanielWeitenauer
  - Fehlermeldung im SystemLog, wenn HTTP-GET Indexierung scheitert.
  - Plaintext PlugIn: Änderung der Einstellungen muss Meldung zum Suchindex erneuern bringen #86
  - "Frontend mode" entfernt #93 @Web-Work24 , @skerbis , @others
  - Leerzeilen aus dem Standard-Plaintext entfernt
  - Schrittweise Indexierung indexiert jetzt auch Medienpool Dateien #92 @skerbis
  - Notices entfernt beim Speichern von geänderten Einstellungen
  - Einstellung "Output Filter anwenden" entfernt ( wird jetzt immer angewendet )
  - Darstellung der Einstellungen für Datei-Indexierung verbessert

## Version 6.1.5 (2017-02-17)
  - Extensionpoint "SLICE_SHOW" ist wirklich nicht mehr nötig --> entfernt, thx @darwin26
  - issue #77 Der Search-Highlighter zeichnet auch im <title> Tag aus,  thx@DanielWeitenauer
  - issue #76 Maximale Trefferanzahl prüfen, thx @DanielWeitenauer
  - Fixes am "frontend mode" @skerbis

## Version 6.1.4 (2017-01-10)
  - PHP 7.1 Anpassung dont [] a ""

## Version 6.1.3 (2016-12-27)
  - Ähnlichkeitssuche bei konjunktiver Suche (AND) jetzt korrekt implementiert
  - Hinweis dass der Suchindex erneuert werden muss, wenn Ähnlichkeitssuche eingeschaltet wird

## Version 6.1.2 (2016-11-21)
  - beim Löschen von Tabellen wird jetzt `TRUNCATE` benutzt, damit die autoincrementwerte zurückgesetzt werden @Flo
  - update.php löscht die alten Plugins "search_highlighter" und "reindex" aus den 5er Versionen

## Version 6.1.1 (2016-11-17)
  - neue Funktion `searchInCategoryTree`, die die Suche auf alle Unterkategorien einer Kategorie beschränkt ( nützlich bei multi-domain sites ) Auf Anregung von @alex_wenz, thx
  - Bezeichnungen im Backend geändert (hoffentlich verbessert)

## Version 6.1.0 (2016-11-16)
  - neuer alter "Frontend mode" als Fix für #66
  - "indiziern" -> "indexieren"
  - PHP Zugriffslevel gesetzt private/public/protected
  - **ACHTUNG**: Funktion `doSearchArticles()` deprecated, wird in nächster Version entfernt, bitte `setSearchAllArticlesAnyway()` nutzen
  - Funktion `excludeArticle()` umbenannt in `unindexArticle()`
  - Backend-Message "Suchindex muss erneuert werden" wird jetzt auch bei geänderten Werten angezeigt, die ein array sind.
  - diverse ungenutze Variablen entfernt (indexUnknownFileExtensions, indexMissingFileExtensions)

## Version 6.0.1 (2016-11-05)
  - Fehler beim Indexieren per HTTP und Verwendung von YRewrite behoben
  - statt `file_get_contents` wird jetzt `rex_socket` verwendet

## Version 6.0.0 (2016-10-21)
  - Fehler beim Indexieren von PDFs behoben, Einstellungen umgestellt

## Version 6.0.0-rc1 (2016-10-10)
  - Re-indizierung jetzt per cronjob möglich

## Version 5.9.1 (2016-09-24)
  - Methode cologne_phon() durch soundex_ger() ersetzt (wegen Lizenz, jetzt BSD)

## Version 5.9.0 (2016-09-24)
  - Plugin Search Highlighter und Reindex in das AddOn eingebaut

## Version 5.8.2 (2016-09-18)
  - Viele Bugs beseitigt
  - Verbesserte Doku (wie yform-docs)

## Version 5.8.1 (2016-09-02)
  - Dokumentation als plugin

## Version 5.8.0 (2016-09-01)
  - Frontend-Mode ausgebaut

## Version 5.7.9 (2016-08-31)
  - Portierung auf R5
  - Umbenennung von **"RexSearch"** in **"Search it"**
  - die sql->escape Methode umgibt das Ergebnis mit single Quotes, was alle SQL Abfragen fehlerhaft machte.
  - "Frontend-Mode" und "outputfilter anwenden" ging so nicht - rex::setProperty('redaxo')
  haben dann aber geholfen.
  - Beim indexieren per HTTP musste ich "rex_url::init(new rex_path_default_provider('/', 'redaxo', true))" verwenden
  - PDF2TXT funzt nicht
  - in "function indexArticle" musste die neue clang-Objektstruktur beachtet werden
  - den re-index-Link im Backend beim Artikelmenu kann man so nicht mehr setzen, weil der EXTENSIONPOINT so nicht mehr existiert

## Version 0.7.9 (2011)
  - Speicherung von indexierten Spalten bei der Indexierung von Artikeln
  - Verbesserung der getHighlightedText-Methode: Textpassagen kommen nicht mehr
    doppelt vor

## Version 0.7.8 (2011-06-29)
  - Plaintext-Plugin um die Möglichkeit erweitert, mit Textile zu parsen

## Version 0.7.7 (2011-06-08)
  - Bug bei der automatischen Indexierung behoben

## Version 0.7.6 (2011-06-01)
  - Methode cologne_phone() von Fehlern behoben (z. B. ungültige Arrayindexzugriffe)
  - XSS-Bug im Stats-Plugin beheben
  - Löschen des Suchindex bei der schrittweisen Indexierung erst nach dem OK-Klick
  - Meldung, wenn bei der schrittweisen Indexierung keine Datensätze indexiert werden können/müssen (http://www.redaxo.org/de/forum/post92638.html#p92638)
  - jedes Vorkommen der Konstante SEARCH_IT_FILE_XPDFERR_PDFPERM zu SEARCH_IT_FILE_XPDFERR_PERM geändert

## Version 0.7.5 (2011-03-30)
  - Fehler bei der Indexierung und Suche innerhalb von Datenbankspalten
    behoben
  - Sprachabhängigkeit bei der Ähnlichkeitssuche berücksichtigt
  - Indexierung der Keywords verbessert
  - Plugin "Search Highlighter"

## Version 0.7.4 (2010-12-05)
  - Nach jeder automatischen (Neu-)Indexierung wird nun der Suchcache
    gelöscht

## Version 0.7.3 (2010-11-26)
  - Fremd-ID (fid) in der Suchindextabelle für Werte aus der Artikel-
    tabelle ist wieder die Artikel-ID
    -> Bug bei der Gruppierung von Suchergebnissen behoben

## Version 0.7.2 (2010-11-12)
  - Bug bei Indexierung von Medienpooldateien behoben (Kategorie-ID wurde
    nicht übergeben)
  - JS-Nachfrage, ob schrittweise Indexierung wirklich gestartet werden
    soll, verbessert
  - Neue Methode RexSearch::searchInFileCategories($_ids):
    Übergabe von Medienpoolkategorie-IDs, in denen gesucht werden soll
  - Neue Methode RexSearch::setWhere($_where):
    zusätzliche WHERE-Bedingungen für die SQL-Suchabfrage
  - Bug bei der Auflistung der Verzeichnisse auf der Einstellungsseite
    behoben

## Version 0.7.1 (2010-10-20)
  - Neue Methode: RexSearch::getMinFID()
  - Bug bei Ermittlung der minimalen FID behoben

## Version 0.7 (2010-10-06)
  - Name des AddOns in RexSearch geändert
  - Ausgabe der schrittweisen Indexierung bei mehrsprachigen REDAXO-
    installationen verbessert

## Version 0.6.1 (2010-10-04)
  - Datenbankfeld "values" hinzugfügt,
    indexierte Spalten einer Datenbanktabelle werden in dieses Feld
    ein getragen und stehen bei der Ausgabe der Suchergebnisse zur
    Verfügung
  - automatische Indexierung optimiert (rexsearch_handle_extensionpoint)

## Version 0.6 (2010-09-16)
  - Datenbankfeld "fid" vom Typ INT zum Typ VARCHAR(255) geändert
  - DEFAULT CHARSET der DB-Tabellen in "utf8" geändert
  - Funktionen json_encode und json_decode für PHP < 5.2 hinzugefügt
  - Bei der Datenbankspalten-Indexierung wird das ID-Feld
    automatisch ermittelt und und der Wert in die DB-Spalte "fid"
    eingetragen
  - Unterstützung für Clustered Primary Keys für das Feld fid
    hinzugefügt, bei zusammengesetzten Primary Keys werden diese
    JSON-kodiert abgespeichert
  - Es können nicht mehr nur Text-, Char- und Varchar-Spalten,
    sondern DB-Spalten jeden Typs indexiert werden
  - Update-Möglichkeit von RexSearch 0.5.4 auf 0.6 durch reinstallieren
    (ohne Datenverlust)
  - JS-Nachfrage, ob schrittweise Indexierung wirklich gestartet werden
    soll, hinzugefügt

## Version 0.5.4 (2010-09-14)
  - Möglichkeit, bei der Indexierung von Artikelinhalten
    den Extension Point "OUTPUT_FILTER" aufzurufen, implementiert
    (http://forum.redaxo.de/sutra84454.html#84454)
