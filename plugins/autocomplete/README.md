# Installation vom Autocomplete

Das Plugin stellt das "Suggest"-Script für die Autovervollständigung bei der Suche im Frontend zur Verfügung und generiert einen Code welcher im Template eingebunden werden muss.

## Requirements

* Installiertes jQuery im Frontend 
* Bei der Nutzung eines HTML Minify, beispielsweise über das Addon Minify, muss das Suchhandler Template in die Blacklist aufgenommen werden.


## Installation

1. Über Installer laden oder ZIP-Datei im Plugin-Ordner der search_it entpacken, der Ordner muss „autocomplete“ heißen.
2. Plugin installieren und aktivieren
3. Modul und Template aus dem Plugin heraus installieren
4. Einen Suchhandle Artikel mit Template (Suchhandler) anlegen und dort das Modul (Suchhandler) hinzufügen
5. Konfiguration im Plugin vornehmen und speichern. Wichtig: Neu angelegter Suchhandler Artikel muss gesetzt werden!
6. Den generierten Code für das Template herauskopieren und in das Template, welches im Suchausgabeartikel verwendet wird, vor dem schließenden `</body>` Tag hinzufügen
7. Sollte die Suche überall verwendet werden, beispielsweise im Kopf der Seite, muss der generierte Code in das entsprechende Template hinzugefügt werden
8. Optional: CSS und JS Datei können auch per Minify eingebunden werden 


## Lizenz

"Autocomplete" von Manétage steht unter MIT Lizenz.

## Rechtliches
Verwendung auf eigene Gefahr. 


## Autor

**Manétage** - Ronny Kemmereit / Pascal Schuchmann
* http://www.manetage.de

**Friends Of REDAXO**

* http://www.redaxo.org
* https://github.com/FriendsOfREDAXO










