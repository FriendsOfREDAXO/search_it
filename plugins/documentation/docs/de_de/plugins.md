# Plugins

## Autocomplete

Das Plugin stellt das "Suggest"-jQuery-PlugIn für die Autovervollständigung bei der Suche im Frontend zur Verfügung und generiert einen Code, welcher im Template eingebunden werden muss.

### Requirements

* jQuery im Frontend
* Ein Suchformular, das die HTML-Klasse `search_it-form`, sowie ein HTML-Eingabefeld für die Suche mit dem Namen `search` enthält.

### Installation

1. Über Installer laden oder ZIP-Datei im Plugin-Ordner der search_it entpacken, der Ordner muss „autocomplete“ heißen.
2. Plugin installieren und aktivieren
3. Konfiguration im Plugin vornehmen und speichern
4. Den generierten Code für das Template herauskopieren und in das Template, welches für das Suchfeld verwendet wird, vor dem schließenden `</body>`-Tag hinzufügen
5. Sollte das Suchfeld überall verwendet werden, beispielsweise im Kopf der Seite, muss der generierte Code in das entsprechende Template hinzugefügt werden
6. Optional: CSS- und JS-Datei in den eigenen Frontend-Prozess einbauen (z.B. per Minify-AddOn oder im Bimmelbam-Workflow von FriendsOfREDAXO)

## Lizenz

MIT

## Credits

**Manetage** - Ronny Kemmereit / Pascal Schuchmann
* https://www.manetage.de

**Friends Of REDAXO**

* https://www.redaxo.org
* https://github.com/FriendsOfREDAXO

## Plaintext

Das Plugin Plaintext reduziert die Artikelinhalte auf reinen Text und entfernt dabei alle HTML-Tags.
Darüberhinaus können CSS-Klassen von der Filterung ausgenommen werden und mit Hilfe Regulärer Ausdrücke Ersetzungen vorgenommen werden.
Die Reihenfolge dieser Aktionen kann ebenfalls beeinflusst werden.

### CSS-Selektoren

Hier können CSS-Selektoren angegeben werden. Alle Elemente, die darauf passen, werden dann aus dem Plaintext entfernt.

### Reguläre Ausdrücke

Immer zwei Zeilen bestimmen eine Ersetzungsregel. Die erste enthält den Regulären Ausdruck (mit Begrenzungszeichen)und die zweite Zeile den einzufügenden Inhalt.
Bsp.:

```text
~<h1>.+</h1>~
(Leerzeile)
~abc~
d
```

Dieses Beispiel entfernt alle h1-Überschriften und ersetzt alle Vorkommen von "abc" durch "d".

### Textile parsen

Wenn die Artikel Textile-Markup enthalten, kann es gewünscht sein, dieses erst zu parsen.

### HTML-Tags entfernen

Aktiviert man diese Option, so werden alle HTML-Tags entfernt.

### Standard-Plaintext-Konvertierung durchführen

Diese Option bestimmt, ob der Plaintext auch Standard-Prozedur zur Generierung des Plaintext durchlaufen soll.
Dies umfasst die Entfernung des 'HTML-head' Bereichs und aller `<script>`-Tags, der Tags
`address|blockquote|center|del|dir|div|dl|fieldset|form|h1|h2|h3|h4|h5|h6|hr|ins|isindex|menu|noframes|noscript|ol|p|pre|table|ul`, sowie die Umwandlung aller HTML-Entities (z.B. "&shy;") in Text.

## Stats

Das Plugin Stats sammelt Daten zur internen Suche und gibt diese als Statistik aus.
