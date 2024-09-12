** das PlugIn ist deprecated. Es wird in Version 7 entfernt.
Bitte in Zukunft die eingebaute autocomplete-Funktion nutzen **

# Installation Autocomplete

Das Plugin stellt das "Suggest"-jQuery-PlugIn für die Autovervollständigung bei
der Suche im Frontend zur Verfügung und generiert einen Code welcher im Template
eingebunden werden muss.

## Requirements

* Installiertes jQuery im Frontend
* Funktionierendes Suchformular, das die HTML-Klasse "search_it-form",
  sowie ein HTML-Eingabefeld für die Suche mit dem Namen "search".

## Installation

1. Über Installer laden oder ZIP-Datei im Plugin-Ordner der search_it entpacken,
   der Ordner muss „autocomplete“ heißen.
2. Plugin installieren und aktivieren
3. Konfiguration im Plugin vornehmen und speichern
4. Den generierten Code für das Template herauskopieren und in das Template,
   welches für das Suchfeld verwendet wird, vor dem schließenden `</body>` Tag
   hinzufügen
5. Sollte das Suchfeld überall verwendet werden, beispielsweise im Kopf der
   Seite, muss der generierte Code in das entsprechende Template hinzugefügt
   werden
6. Optional: CSS und JS Datei in den eigenen Frontend_prozess einbauen ( z.B.
   per Minify oder im Bimmelbam )

## Lizenz

"Autocomplete" von Manétage steht unter MIT Lizenz.

## Rechtliches

Verwendung auf eigene Gefahr.

## Autor

**Friends Of REDAXO**

* http://www.redaxo.org
* https://github.com/FriendsOfREDAXO

## Credits

**Manetage** - Ronny Kemmereit / Pascal Schuchmann
* http://www.manetage.de
