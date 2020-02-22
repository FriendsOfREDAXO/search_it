# Indexierung

`Search it` erstellt den Index, in dem es die Artikel der Website im Frontend aufruft und den Artikelinhalt indexiert.
D.h. im ersten Schritt werden nur im Frontend sichtbare Inhalte gefunden. Insbesondere werden also Passwort-geschützte Inhalte nicht im Index landen.

Über die Auswahl von Datenbanktabellenspalten im Register "Zusätzl. Datenquellen" können auch nicht im Frontend sichtbare Inhalte indexiert werden.

> **Hinweis:** Voraussetzung für die Artikelindexierung ist, dass die Artikel im Frontend erreichbar sind und bspw. nicht durch Addons oder aus anderen Gründen der Aufruf der Seite für nicht eingeloggte Nutzer blockiert wird. Addons wie bspw. `maintenance` oder ein ungültiges SSL-Zertifikat können die Indexierung blockieren.

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
SLICE_ADDED, SLICE_DELETED, SLICE_UPDATED|Der Artikel wird neu indexiert
