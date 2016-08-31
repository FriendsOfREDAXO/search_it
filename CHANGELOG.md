Änderungen von search_it
==========================

###Version 5.7.9 (2016)
  + Portierung auf R5
  + Umbenennung von **"RexSearch"** in **"search_it"**
  + die sql->escape Methode umgibt das Ergebnis mit single Quotes, was alle SQL Abfragen fehlerhaft machte.
  + "Frontend-Mode" und "outputfilter anwenden" ging so nicht - rex::setProperty('redaxo') 
  haben dann aber geholfen.
  + Beim indizieren per HTTP musste ich "rex_url::init(new rex_path_default_provider('/', 'redaxo', true))" verwenden
  + PDF2TXT funzt nicht
  + in "function indexArticle" musste die neue clang-Objektstruktur beachtet werden
  + den re-index-Link im Backend beim Artikelmenu kann man so nicht mehr setzen, weil der EXTENSIONPOINT so nicht mehr existiert

###Version 0.7.9 (2011)
  + Speicherung von indexierten Spalten bei der Indexierung von Artikeln
  + Verbesserung der getHighlightedText-Methode: Textpassagen kommen nicht mehr
    doppelt vor

###Version 0.7.8 (2011-06-29)
  + Plaintext-Plugin um die Möglichkeit erweitert, mit Textile zu parsen

###Version 0.7.7 (2011-06-08)
  + Bug bei der automatischen Indexierung behoben

###Version 0.7.6 (2011-06-01)
  + Methode cologne_phone() von Fehlern behoben (z. B. ungültige Arrayindexzugriffe)
  + XSS-Bug im Stats-Plugin beheben
  + Löschen des Suchindex bei der schrittweisen Indexierung erst nach dem OK-Klick
  + Meldung, wenn bei der schrittweisen Indexierung keine Datensätze indexiert werden können/müssen (http://www.redaxo.org/de/forum/post92638.html#p92638)
  + jedes Vorkommen der Konstante SEARCH_IT_FILE_XPDFERR_PDFPERM zu SEARCH_IT_FILE_XPDFERR_PERM geändert

###Version 0.7.5 (2011-03-30)
  + Fehler bei der Indexierung und Suche innerhalb von Datenbankspalten
    behoben
  + Sprachabhängigkeit bei der Ähnlichkeitssuche berücksichtigt
  + Indexierung der Keywords verbessert
  + Plugin "Search Highlighter"

###Version 0.7.4 (2010-12-05)
  + Nach jeder automatischen (Neu-)Indexierung wird nun der Suchcache
    gelöscht

###Version 0.7.3 (2010-11-26)
  + Fremd-ID (fid) in der Suchindextabelle für Werte aus der Artikel-
    tabelle ist wieder die Artikel-ID
    -> Bug bei der Gruppierung von Suchergebnissen behoben

###Version 0.7.2 (2010-11-12)
  + Bug bei Indexierung von Medienpooldateien behoben (Kategorie-ID wurde
    nicht übergeben)
  + JS-Nachfrage, ob schrittweise Indexierung wirklich gestartet werden
    soll, verbessert
  + Neue Methode RexSearch::searchInFileCategories($_ids):
    Übergabe von Medienpoolkategorie-IDs, in denen gesucht werden soll
  + Neue Methode RexSearch::setWhere($_where):
    zusätzliche WHERE-Bedingungen für die SQL-Suchabfrage
  + Bug bei der Auflistung der Verzeichnisse auf der Einstellungsseite
    behoben

###Version 0.7.1 (2010-10-20)
  + Neue Methode: RexSearch::getMinFID()
  + Bug bei Ermittlung der minimalen FID behoben

###Version 0.7 (2010-10-06)
  + Name des Addons in RexSearch geändert
  + Ausgabe der schrittweisen Indexierung bei mehrsprachigen Redaxo-
    installationen verbessert

###Version 0.6.1 (2010-10-04)
  + Datenbankfeld "values" hinzugfügt,
    indexierte Spalten einer Datenbanktabelle werden in dieses Feld
    ein getragen und stehen bei der Ausgabe der Suchergebnisse zur
    Verfügung
  + automatische Indexierung optimiert (search_it_handle_extensionpoint)

###Version 0.6 (2010-09-16)
  + Datenbankfeld "fid" vom Typ INT zum Typ VARCHAR(255) geändert
  + DEFAULT CHARSET der DB-Tabellen in "utf8" geändert
  + Funktionen json_encode und json_decode für PHP < 5.2 hinzugefügt
  + Bei der Datenbankspalten-Indexierung wird das ID-Feld
    automatisch ermittelt und und der Wert in die DB-Spalte "fid"
    eingetragen
  + Unterstützung für Clustered Primary Keys für das Feld fid
    hinzugefügt, bei zusammengesetzten Primary Keys werden diese
    JSON-kodiert abgespeichert
  + Es können nicht mehr nur Text-, Char- und Varchar-Spalten,
    sondern DB-Spalten jeden Typs indexiert werden
  + Update-Möglichkeit von RexSearch 0.5.4 auf 0.6 durch reinstallieren
    (ohne Datenverlust)
  + JS-Nachfrage, ob schrittweise Indexierung wirklich gestartet werden
    soll, hinzugefügt
    
###Version 0.5.4 (2010-09-14)
  + Möglichkeit, bei der Indexierung von Artikelinhalten
    den Extension Point "OUTPUT_FILTER" aufzurufen, implementiert
    (http://forum.redaxo.de/sutra84454.html#84454)

    






