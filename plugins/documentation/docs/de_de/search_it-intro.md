##Zu dieser Dokumentation

Diese Wikiseite erklärt die Konfiguration des Addons, die Schnittstelle der search_it-Klasse und zeigt anhand von Beispielen, wie Suchmodule aufgebaut werden können.

##Allgemein/Voraussetzungen

Das Addon Search it fügt Redaxo eine Volltextsuche hinzu.

Dabei werden Artikel und auswählbare Datenbankspalten in einer DB-Tabelle des Addons gespeichert.

Suchanfragen können außerdem in einer Cache-Tabelle gespeichert werden. Das spart Serverrechenleistung und führt zur schnelleren Anzeige von Suchergebnissen.

Das Addon setzt PHP >= 5.5, "MySQL >= 5.1" und Redaxo >= 5.2 voraus.

##Funktionen/Merkmale
- Volltextsuche für Artikel und beliebige Datenbankspalten mehrsprachenfähig
- Suche im Originaltext, im Plaintext oder in beiden möglich
- Suchmodi: OR (mindestens ein Suchwort muss enthalten sein) und AND (alle Suchworte müssen enthalten sein)
- Suche mit LIKE oder MATCH AGAINST
- Verschiedene Möglichkeiten Suchwörter hervorzuheben
- Einstellen einer maximalen Trefferanzahl
- Einstellen der maximalen Zeichenanzahl für den Teaser-Text
- automatisches (De)Indexieren von Artikeln und Spalten soweit möglich Caching von Suchanfragen
- Eingabe von Blacklistwörtern
- Ausschluss von Artikeln oder Kategorien möglich
- Ähnliche Wörter werden von der Suche automatisch vorgeschlagen
- Suchbegriffe können mit Anführungszeichen (") umschlossen werden
- Suchbegriffe können mit einer beliebigen Anzahl von vorangestellten Pluszeichen (+) höher gewichtet werden (wirkt sich auf die Reihenfolge der Ergebnisse aus)
- zwei verschieden Arten, um den Suchindex zu erneuern (wenn es Probleme mit der max_execution_time gibt)
- für Entwickler interessant: über die Methoden der search_it-Klasse kann die Suche verfeinert bzw. für mehrere Module unterschiedlich angepasst werden, außerdem ist z. B. eine Pagination von Suchergebnissen möglich
- Angabe von Kategorien, Artikeln und DB-Spalten, in denen gesucht werden soll
- Durchsuchen von beliebigen Ordnern mit beliebigen Dateien möglich
- einfache Konfiguration im Backend
- einstellbar, wer das Addon konfigurieren darf 


##Installation

Zur Installation des Addons muss sich der Ordner search_it im Addon-Ordner von Redaxo befinden.

Über das Backend kann das Addon dann installiert und aktiviert werden.

Das Addon legt dabei 4 Datenbanktabellen an: Eine für die Indexierung von Artikeln und DB-Spalten, zwei für den Suchcache und eine für die Ähnlichkeitssuche.

Nach der Installation muss der Index einmal vollständig oder schrittweise erstellt werden.
 
 
 
#Konfiguration


##Suchmodi

- Logischer Suchmodus: Sie können hier den logischen Suchmodes bestimmen. Dies wirkt sich auf die Suche nach mehreren Worten aus.
    - Konjunktive Suche (AND): Alle Wörter müssen im zu durchsuchenden Text enthalten sein.
    - Disjunktive Suche (OR): Mindestens ein Wort muss im zu durchsuchenden Text enthalten sein. 

- Textmodus: Der Textmodus bestimmt, welche Texte durchsucht werden sollen.
    - Durchsuche Text ohne HTML-Tags (Plain)
    - Durchsuche Text mit HTML-Tags (HTML)
    - Durchsuche beides (HTML und Plain) 

- Ähnlichkeitssuche
    - Deaktivieren
    - Soundex
    - Metaphone
    - Kölner Phonetik
    - Alle 

-MySQL-Suchmodus
    - LIKE (findet auch Teilwörter, aber langsamer)
    - MATCH AGAINST (findet nur ganze Wörter, schneller) 

Obwohl die genauere Suche mit MATCH AGAINST weniger Suchergebnisse präsentiert, wird der Einsatz dieser Methode empfohlen, da die Suche dadurch beschleunigt wird. Das Manko der genaueren Suche - wenn man es denn so empfindet - kann leicht über die Ähnlichkeitssuche ausgeglichen werden.


##Indexierung

- Art der Indexierung
     - Indexierung der Artikel über eine HTTP-GET-Anfrage
     - Indexierung der Artikel über den Redaxo-Cache (ohne Template, nur der Artikel)
     - Indexierung der Artikel über den Redaxo-Cache (mit Template, liefert das gleiche Ergebnis wie per HTTP-GET-Anfrage) 
- Offline-Artikel indexieren?
- Artikel (ADD, EDIT, DELETE) automatisch (de)indexieren
- Extension Point "OUTPUT_FILTER" aufrufen 
 
##Erscheinungsbild des Highlight-Texts
 
- Start-Tag
- End-Tag
- Maximale Trefferanzahl
- Maximale Zeichenanzahl für Teaser
- Maximale Zeichenanzahl um hervorgehobene Suchbegriffe herum
- Erscheinungsbild des Highlight-Texts
    - ab Anfang des Satzes, in dem mindestens einer der Suchbegriffe auftaucht
    - ab Anfang des Absatzes, in dem mindestens einer der Suchbegriffe
    - alle gefundenen Suchbegriffe werden mit den sie umgebenden Wörtern dargestellt
    - für jeden gefundenen Suchbegriff wird genau eine Textstelle wiedergegeben
    - als Teaser, in dem eventuell vorkommende Suchbegriffe hervorgehoben sind
    - als Array mit allen Suchbegriffen und Textstellen 

##Wörter, Kategorien und Artikel von der Suche ausschließen

- Wörter: Ein Liste von Wörtern, nach denen nicht gesucht werden kann/darf bzw. die keine Suchergebnisse produzieren.
- Kategorien: Hier können alle Kategorien ausgewählt werden, die nicht durchsucht werden sollen.
- Artikel: Hier können alle Artikel ausgewählt werden, die nicht durchsucht werden sollen. 

##Datenbankspalten in die Suche einschließen

- `<table>.<column>`: Es können DB-Spalten ausgewählt werden, die auch durchsucht werden sollen. Hierfür bietet sich z. B. die Spalte article.art_description des Addons Metainfo an. 

##Dateisuche

- Dateiendungen
- Medienpool indexieren
- Verzeichnistiefe: Gibt an bis zu welcher Tiefe Dateien in den ausgewählten Verzeichnissen indexiert werden sollen.
- Folgende Ordner in die Suche einschließen: Wählen Sie die Ordner und Unterordner aus, die indexiert werden sollen. Javascript muss aktiviert sein.
 
 
 
 ##Rückgabe / Ergebnisarray
 
 Das Ergebnisarray, das von der search-Methode zurückgegeben wird, hat folgenden Aufbau:
 
hits: ein Array der Treffer, wobei jeder Treffer selbst ein Array mit folgendem Inhalt ist:
    id: die ID des Suchergebnis´ in der Tabelle searchindex
    fid: die Fremd-ID, von dem Datensatz, der indexiert wurde (z. B. die Artikel-ID)
    table: die DB-Tabelle, von der indexiert wurde
    column: die DB-Spalte, von der indexiert wurde (NULL, wenn es ein indexierter Artikel ist)
    type: db_column, article oder file
    clang: Sprache
    fileext: Dateiendung
    filename: Pfad mit Dateiname
    unchangedtext: der unveränderte Originaltext
    plaintext: der von HTML- und PHP-Tags befreite Text
    teaser: Teaser (Plaintext gekürzt auf die Anzahl der maximalen Teaserzeichen)
    highlightedtext: Text oder Array mit hervorgehobenen Suchbegriffen innerhalb von Textstellen
    article_teaser: wenn eine Datenbankspalte aus der Tabelle rex_article indexiert wurde, dann steht hier der Teaser des Artikels
    values: wenn mehrere Spalten der gleichen Tabelle indexiert sind, so stehen deren Werte für jeden Datensatz als Array zur Verfügung 
keywords: Array mit allen Suchbegriffen
searchterm: der eingegebene Suchterm
sql: die genutzte SQL-Abfrage
blacklisted: Array mit "schwarzen" Wörtern, die in der Suchabfrage genutzt wurden
time: Dauer der Suche
count: Anzahl aller Datensätze, die ohne LIMIT-Klausel gefunden werden könnten
hash: Hash, unter dem die Suche im Cache gespeichert wurde
simwordsnewsearch: Vorschlag für ähnliche Suchbegriffe (nur, wenn die Suche kein Ergebnis brachte)
simwords: ein Array mit Wörtern, die den Suchbegriffen ähneln, wobei die Schlüssel dieses Arrays die eingegebenen "falschen" Wörter sind und die Werte wiederum ein Array, das wie folgt aufgebaut ist:
    typedin: noch einmal das "falsche" Wort
    keyword: das "richtige" Schlüsselwort
    count: Anzahl, wie oft das "richtige" Schlüsselwort gefunden wurde 
 
 
#Funktionsweise
 
##Automatische Indexierung / Indexerneuerung
 
Eine Automatisch De-(Indexierung) erfolgt im Moment mit folgenden Extension-Points:
 
     ART_DELETED: Wenn ein Artikel gelöscht wird, fliegt er aus dem Suchcache.
     ART_META_UPDATED: Wenn Metainfos geändert wurden, werden alle ausgewählten DB-Spalten aus der Tabelle rex_article neu indexiert.
     ART_ADDED: dito
     ART_STATUS: Ein Artikel, der offline geschaltet wird, wird deindexiert, bei online indexiert.
     ART_UPDATED: wie ART_META_INFO
     CAT_DELETED: Ausgabe einer Meldung, das der Index erneuert werden muss
     CAT_STATUS: Eine Kategorie, die offline geschaltet wird, wird deindexiert, bei online indexiert.
     CAT_ADDED: dito
     CAT_UPDATED: wie ART_META_INFO
     MEDIA_ADDED: Wenn ein Medium hinzugefügt wurde, werden alle ausgewählten DB-Spalten aus der Tabelle rex_file neu indexiert.
     MEDIA_UPDATED: wie MEDIA_ADDED
     SLICE_SHOW: Wichtigster Extensionpoint: Wird ein Artikel verändert (z. B. Inhalt geändert, Slice verschoben, etc...), wird er neu indexiert.
     SLICE_UPDATED:
 
##Ähnlichkeitssuche
 
Die Ähnlichkeitssuche muss im Backend in der Konfigurationsansicht von Search it eingestellt werden.
Ist die Ähnlichkeitssuche aktiviert, baut Search it bei jeder Suche, die einen Treffer ergab, einen Schlagwortindex aus. Dabei wird angenommen, dass Wörter, die zu Suchergebnissen führen, richtig geschrieben sind.
Sollte eine Suche keine Ergebnisse liefern, füllt Search it das Result-Array mit eventuell gefundenen ähnlichen Wörtern und macht auch einen Vorschlag, wie der neue Suchbegriff aussehen könnte.
Um die Ähnlichkeitssuche effektiv einsetzen zu können, empfiehlt es sich, die Suche selbst mit richtigen Schlagwörtern zu füttern. Dadurch sind erste Suchwörter indexiert und die Ähnlichkeitssuche kann bei einer falschen Schreibweise dieser Wörter diese vorschlagen.
 
 
 
##Tipps, Tricks und FAQ
 
Die automatische Indexierung funktioniert nicht für meine ausgewählten Datenbankspalten. Was kann ich tun?
Dieses Problem ist nicht trivial und allumfassend lösbar.
Da das Addon nicht wissen kann, wann eine Datenbankspalte neu indexiert werden muss, kann keine automatische Indexierung erfolgen.
Die Klasse search_it bietet allerdings die Methode indexColumn an. Über diese Methode können Datenbankspalten neu oder wieder indexiert werden.
Müssen die Datenbankspalten nur zu einem bestimmten Datensatz indexiert werden, kann außerdem die ID dieses Datensatzes angegeben werden.
Search it wird dann auch nur den betroffenen Datensatz reindexieren.

Beispiel:
Ein Addon arbeitet mit einer eigenen Datenbanktabelle. Search it soll Inhalte dieses Addons auch automatisch reindexieren.
Da das Addon selbst weiß, wann die Beispieldatenbankspalte "beschreibung" reindexiert werden soll, kann die Methode indexColumn von diesem Addon aufgerufen werden:
 
     $search_it = new search_it;
     $search_it->indexColumn('TABELLE_DES_ADDONS', 'beschreibung', 'id', $datensatz_id);
 
 
Die Methode indexColumn benötigt also 4 Parameter:
    Die Namen der Datenbanktabelle und
    der Datenbankspalte,
    den Namen der identifizierenden Datenbankspalte und
    die ID des Datensatzes, der aktualisiert wurde.
 
 
Wörter mit Umlauten und Sonderzeichen werden bei der Suche nicht gefunden. Warum ist das so und wie kann ich das beheben?
Es ist sehr wahrscheinlich, dass diese Sonderzeichen als Entitäten kodiert vorliegen (z. B. ü als &uuml;).
Das Problem tritt häufig im Zusammenhang mit TinyMCE auf.
Es gibt zwei Möglichkeiten, um das Problem zu lösen, wobei erstere zu bevorzugen ist:
    Die Zeichen müssen "roh" in die Datenbank.
    Um das zu erreichen, sollte UTF-8 als Zeichenkodierung genutzt werden.
    Außerdem muss der TinyMCE so eingestellt werden, dass er die Zeichen auch roh speichert. Das geht mit der Option entity_encoding : "raw".
    Wird der TinyMCE nicht genutzt, dann muss das verwendete Eingabemodul entsprechend konfiguriert oder programmiert werden, dass die Daten roh in die Datenbank kommen.
    Nur wenn die erste Lösungsmethode absolut nicht eingesetzt werden kann, kann man die Sucheingabe im Suchmodul mit der PHP-Funktion htmlentities vorbehandeln, bevor sie an die search-Methode weitergereicht wird.
In diesem Zusammenhang sollte auch ein Fehler im Quellcode von Redaxo behoben werden, damit die Daten wirklich UTF-8-kodiert in die Datenbank geschrieben werden: http://forum.redaxo.de/ftopic12127.html
 
 
##Ich komme mit der Installation nicht zurecht. Gibt es eine Schritt-für-Schritt-Anleitung?
Ja! ;-) 

Search it herunterladen: http://www.redaxo.de/180-0-addon-details.html?addon_id=587
Ordner search_it in das Addon-Verzeichnis der Redaxo-Installation kopieren/entpacken
das Addon im Backend installieren und aktivieren
auf der Seite des Addons gewünschte Einstellungen vornehmen
auf der Unterseite "Indexierung" die Indexierung starten (ich empfehle die schrittweise Indexierung)
das Suchformular und eines der Suchmodule einrichten und das Suchmodul in einen Artikel einbinden
Wichtig: Im Suchformular muss die Artikel-ID auf den Artikel mit dem Suchmodul verweisen!
Suche testen 
 
 
 
##Wie kann ich sprachabhängig suchen?

Search it sucht per Standard in allen Sprachen.
Um sprachabhängige Suchen zu erlauben, muss der Klasse die Sprach-ID der Sprache, in der gesucht werden soll, übergeben werden.
Das geht in einem Suchmodul am einfachsten über die durch Redaxo definierte Konstante REX_CLANG_ID.
Achtung: Die Sprach-ID sollte im Ergebnismodul $search_it = new search_it(REX_CLANG_ID); und im Suchformular <input type="hidden" name="clang" value="REX_CLANG_ID" /> gesetzt werden.


