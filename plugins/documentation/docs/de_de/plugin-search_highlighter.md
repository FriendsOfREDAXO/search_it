#Search Highlighter

Das Plugin Search Highlighter nimmt übergebene Suchbegriffe entgegen und markiert diese innerhalb der Seite. Die Formatierung der gefundenen Suchbegriffe kann im Backend per CSS gesteuert werden.

Die Option Tag um die Suchbegriffe bietet die Möglichkeit frei zu wählen wie die gefundenen Suchbegriffe umschlossen werden sollen.
Die Option Class fügt dem ausgewählten Tag neben der Standardklasse class_search_685 weitere Klassen hinzu. Mehre Klassen müssen durch ein Leerzeichen getrennt werden.
Mit inline CSS ist es möglich Stilanweisungen direkt in den Tag zu schreiben.
Eingabe font-weight: bold; erzeugt style="font-weight: bold;"
Mit Stil CSS einbinden kann man angeben ob die PHP/CSS Datei für die verschiedenen Stile im Kopf der Seite eingebunden werden soll.

Vordefinierte Stile:

- `stil1` formatiert den Text schwarz, fett, gelber Hintergrund
- `stil2` formatiert den Text kursiv und mit einer größeren Schriftgröße
- `stilEigen` hier können im Backend eigene CSS Angaben gemacht werden 



Damit die Suche den Suchbegriff an die aufgerufene Seite übergibt muss das Suchausgabemodul bearbeitet werden. Als Grundlage dient z.b. das Erweiterte Beispielmodul.
Die Änderung betrifft die Ausgabe des Links zur gefundenen Seite.

        <h4><a href="'. ($url = htmlspecialchars($article->getUrl()) . '&search_highlighter=' . urlencode($_REQUEST['xsearch'])) .'">'.$article->getName().'</a></h4>

Dadruch wird der Parameter search_highlighter an die Seite übergeben und kann dort ausgelesen werden