### Reindexierung von Datenbank-Feldern

Dieses Problem ist nicht trivial und allumfassend lösbar. Da das Addon nicht wissen kann, wann eine Datenbankspalte neu indexiert werden muss, kann keine automatische Indexierung erfolgen.

Die Klasse search_it bietet allerdings die Methode indexColumn an. Über diese Methode können Datenbankspalten neu oder wieder indexiert werden.
Müssen die Datenbankspalten nur zu einem bestimmten Datensatz indexiert werden, kann außerdem die ID dieses Datensatzes angegeben werden.
Search it wird dann auch nur den betroffenen Datensatz reindexieren.

Beispiel:

Ein Addon arbeitet mit einer eigenen Datenbanktabelle. Search it soll Inhalte dieses Addons auch automatisch reindexieren.
Da das Addon selbst weiß, wann die Beispieldatenbankspalte "beschreibung" reindexiert werden soll, kann die Methode indexColumn von diesem Addon aufgerufen werden:
 
```
$search_it = new search_it;
$search_it->indexColumn('tabelle', 'feld', 'id', $datensatz_id);
``` 
 
Die Methode indexColumn benötigt also 4 Parameter:
*    Die Namen der Datenbanktabelle und
*    der Datenbankspalte,
*    den Namen der identifizierenden Datenbankspalte und
*    die ID des Datensatzes, der aktualisiert wurde.
 
 
Es gibt zwei Möglichkeiten, um das Problem zu lösen, wobei erstere zu bevorzugen ist:
* Die Zeichen müssen "roh" in die Datenbank.
    Um das zu erreichen, sollte UTF-8 als Zeichenkodierung genutzt werden.
    Außerdem muss der TinyMCE so eingestellt werden, dass er die Zeichen auch roh speichert. Das geht mit der Option entity_encoding : "raw".

    Wird der TinyMCE nicht genutzt, dann muss das verwendete Eingabemodul entsprechend konfiguriert oder programmiert werden, dass die Daten roh in die Datenbank kommen.
    Nur wenn die erste Lösungsmethode absolut nicht eingesetzt werden kann, kann man die Sucheingabe im Suchmodul mit der PHP-Funktion htmlentities vorbehandeln, bevor sie an die search-Methode weitergereicht wird.
