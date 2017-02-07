# Automatische Reindexierung 

`Search it` greift auf folgende Extension Points zurück, um Änderungen an den Inhalten von Redaxo-Artikeln zu erfassen:

* `ART_DELETED`
* `ART_META_UPDATED`
* `ART_ADDED`
* `ART_STATUS`
* `ART_UPDATED`
* `CAT_DELETED`
* `CAT_STATUS`
* `CAT_ADDED`
* `CAT_UPDATED`
* `MEDIA_ADDED`
* `MEDIA_UPDATED`
* `SLICE_UPDATED`
* `SLICE_DELETED`
* `SLICE_ADDED`
* `SLICE_SHOW`

## Reindexierung von Artikeln via Cronjob

Falls notwendig, kann eine Reindexierung von Artikeln auch über einen Cronjob angesteuert werden. Dazu gibt es seit Search it `6.0` einen eigenen Cronjob-Typ, der im `Cronjob`-AddOn ausgewählt werden kann.

Eine Indexierung kann auch manuell angestoßen werden.

## Reindexierung von Datenbank-Feldern

Die Klasse `search_it` bietet allerdings die Methode `indexColumn` an. Über diese Methode können Datenbankspalten neu oder wieder indexiert werden. Müssen die Datenbankspalten nur zu einem bestimmten Datensatz indexiert werden, kann außerdem die ID dieses Datensatzes angegeben werden. Search it wird dann auch nur den betroffenen Datensatz reindexieren.

**Am Beispiel eines AddOns**

Ein AddOn arbeitet mit einer eigenen Datenbank-Tabelle, hier: `table`. Search it soll Inhalte dieses AddOns auch automatisch reindexieren. Da das AddOn selbst weiß, wann die Beispieldatenbank-Feld `field` reindexiert werden soll, kann die Methode `indexColumn` von diesem AddOn aufgerufen werden:
 
```
$search_it = new search_it;
$search_it->indexColumn('table', 'field'[, 'id'[, $datensatz_id]]);
``` 
 
Die Methode `indexColumn` benötigt daher folgende Parameter:
*    Die Namen der Datenbanktabelle (hier: `table`) und
*    das Datenbank-Feld (hier: `field`),
*    optional der Primärschlüssel (Standard: `id`) und
*    optional die ID des Datensatzes, der reindexiert wird (Standard: alle).
