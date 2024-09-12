# Autocomplete

Es stellt ein "Suggest"-PlugIn für die Autovervollständigung bei
der Suche im Frontend zur Verfügung und generiert einen Code welcher im Template
eingebunden werden muss.

## Vorraussetzungen

* Funktionierendes Suchformular, das die HTML-Klasse "search_it-form",
  sowie ein HTML-Eingabefeld für die Suche mit dem Namen "search" beinhaltet.

## In Betrieb nehmen

1. Autocomplete aktivieren
2. Konfiguration vornehmen und speichern
3. Den generierten Code für das Template herauskopieren und in das Template,
   welches für das Suchfeld verwendet wird, vor dem schließenden `</body>` Tag
   hinzufügen
4. Sollte das Suchfeld überall verwendet werden, beispielsweise im Kopf der
   Seite, muss der generierte Code in das entsprechende Template hinzugefügt
   werden
5. Optional: CSS und JS Datei in den eigenen Frontend_prozess einbauen ( z.B.
   per Minify oder im Bimmelbam )

## Lizenz

"Autocomplete" von Manétage steht unter MIT Lizenz.

## Rechtliches

Verwendung auf eigene Gefahr.

## Autor

**Friends Of REDAXO**

* http://www.redaxo.org
* https://github.com/FriendsOfREDAXO
