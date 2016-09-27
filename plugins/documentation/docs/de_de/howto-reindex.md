# Automatische Reindexierung 

`Search it` greift auf folgende Extension Points zurück, um Änderungen an den Inhalten von Redaxo-Artikeln zu erfassen:


## Reindexierung von Artikeln via Cronjob

Falls notwendig, kann eine Reindexierung von Artikeln auch über einen Cronjob angesteuert werden:

```
<?php
# Dieses Beispiel ist noch nicht funktionstüchtig.
#foreach($search_it->includeColumns() as $table => $columnArray){
#        foreach($columnArray as $column) {
#            $search_it->indexColumn($table, $column);
#        }
#}
?>
```

## Reindexierung von Datenbank-Feldern

Die Klasse `search_it` bietet allerdings die Methode `indexColumn` an. Über diese Methode können Datenbankspalten neu oder wieder indexiert werden. Müssen die Datenbankspalten nur zu einem bestimmten Datensatz indexiert werden, kann außerdem die ID dieses Datensatzes angegeben werden. Search it wird dann auch nur den betroffenen Datensatz reindexieren.

### Allgemein

Falls notwendig, kann eine Reindexierung von Datenbank-Tabellen auch über einen Cronjob angesteuert werden:

```
<?php
# Dieses Beispiel ist noch nicht funktionstüchtig.
#
#$art_sql = rex_sql::factory();
#$art_sql->setTable($search_it->tablePrefix.'article');
#if($art_sql->select('id,clang_id')){
#    foreach($art_sql->getArray() as $art){
#        $search_it->indexArticle($art['id'], $art['clang_id']);
#    }
#}
?>
```

### Für Addons

Ein Addon arbeitet mit einer eigenen Datenbank-Tabelle, hier: `table`. Search it soll Inhalte dieses Addons auch automatisch reindexieren. Da das Addon selbst weiß, wann die Beispieldatenbank-Feld `field` reindexiert werden soll, kann die Methode `indexColumn` von diesem Addon aufgerufen werden:
 
```
$search_it = new search_it;
$search_it->indexColumn('table', 'field'[, 'id'[, $datensatz_id]]);
``` 
 
Die Methode `indexColumn` benötigt daher folgende Parameter:
*    Die Namen der Datenbanktabelle (hier: `table`) und
*    das Datenbank-Feld (hier: `field`),
*    optional der Primärschlüssel (Standard: `id`) und
*    optional die ID des Datensatzes, der reindexiert wird (Standard: alle).
