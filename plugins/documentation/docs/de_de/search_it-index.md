# Automatische Indexierung / Indexerneuerung
 
Eine Automatisch De-(Indexierung) erfolgt im Moment mit folgenden Extension-Points:

Extension Point | Erläuterung
------ | ------ 
ART_DELETED|Wenn ein Artikel gelöscht wird, wird er aus dem Suchcache entfernt.
ART_META_UPDATED, ART_ADDED, CAT_UPDATED, ART_UPDATED|Wenn Metainfos geändert wurden, werden alle ausgewählten DB-Spalten aus der Tabelle rex_article neu indexiert.
ART_STATUS| Ein Artikel, der offline geschaltet wird, wird deindexiert, bei online indexiert.
CAT_DELETED| Ausgabe einer Meldung, dass der Index erneuert werden muss.
CAT_STATUS, CAT_ADDED| Eine Kategorie, die offline geschaltet wird, wird deindexiert, bei online indexiert.
MEDIA_ADDED, MEDIA_UPDATED|Wenn ein Medium hinzugefügt wurde, werden alle ausgewählten DB-Spalten aus der Tabelle rex_file neu indexiert.
SLICE_SHOW| Wird ein Artikel verändert (z. B. Inhalt geändert, Slice verschoben, etc...), wird er neu indexiert.
SLICE_UPDATED|

__todo__: Extension Points aktualisieren