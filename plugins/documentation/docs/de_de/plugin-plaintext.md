# Plaintext

Das Plugin Plaintext reduziert die Artikelinhalte auf reinen Text und entfernt dabei alle HTML-Tags. 
Darüberhinaus können CSS-Klassen von der Filterung ausgenommen werden und mit Hilfe Regulärer Ausdrücke Ersetzungen vorgenommen werden.
Die Reihenfolge dieser Aktionen kann ebenfalls beeinflusst werden.


## CSS-Selektoren
Hier können CSS-Selektoren angegeben werden. Alle Elemente, die darauf passen, werden dann aus dem Plaintext entfernt.

## Reguläre Ausdrücke
Immer zwei Zeilen bestimmen eine Ersetzungsregel. Die erste enthält den Regulären Ausdruck (mit Begrenzungszeichen)und die zweite Zeile den einzufügenden Inhalt.
Bsp.:
```
~<h1>.+</h1>~
(Leerzeile)
~abc~
d
```

Dieses Beispiel entfernt alle h1-Überschriften und ersetzt alle Vorkommen von "abc" durch "d".


## Textile parsen
Wenn die Artikel Textile-Markup enthalten, kann es gewünschtr sein dieses erst zu parsen. 

## HTML-Tags entfernen
Aktiviert man diese Option, so werden alle HTML-Tags entfernt.  

## Standard-Plaintext-Konvertierung durchführen
Diese Option bestimmt, ob der Plaintext auch Standard-Prozedur zur Generierung des Plaintext durchlaufen soll.
Dies umfasst die Entfernung des 'HTML-head' Bereichs und aller 'script'-Tags, der Tags
'address|blockquote|center|del|dir|div|dl|fieldset|form|h1|h2|h3|h4|h5|h6|hr|ins|isindex|menu|noframes|noscript|ol|p|pre|table|ul'
, sowie die Umwandlung aller HTML-Entities (z.B. "&shy;") in Text.