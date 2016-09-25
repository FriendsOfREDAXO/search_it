# Reindexierung von Datenbank-Feldern

Da das Addon nicht wissen kann, wann eine Datenbankspalte neu indexiert werden muss, kann keine automatische Indexierung erfolgen.

Die Klasse search_it bietet allerdings die Methode `indexColumn` an. Über diese Methode können Datenbankspalten neu oder wieder indexiert werden. Müssen die Datenbankspalten nur zu einem bestimmten Datensatz indexiert werden, kann außerdem die ID dieses Datensatzes angegeben werden. Search it wird dann auch nur den betroffenen Datensatz reindexieren.

## Beispiel

Ein Addon arbeitet mit einer eigenen Datenbank-Tabelle `table`. Search it soll Inhalte dieses Addons auch automatisch reindexieren. Da das Addon selbst weiß, wann die Beispieldatenbank-Feld `field` reindexiert werden soll, kann die Methode `indexColumn` von diesem Addon aufgerufen werden:
 
```
$search_it = new search_it;
$search_it->indexColumn('table', 'field', 'id'[, $datensatz_id]);
``` 
 
Die Methode `indexColumn` benötigt also 4 Parameter:
*    Die Namen der Datenbanktabelle (hier: `table`) und
*    das Datenbank-Feld (hier: `field`),
*    der Primärschlüssel (hier: `id`) und
*    optional die ID des Datensatzes, der reindexiert wird.