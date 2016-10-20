#Konfiguration

- [Wartung](#wartung)
- [Einstellungen](#einstellungen)
    - [Suchmodus](#einstellungen-suchmodus)
    - [Suchergebnis](#einstellungen-suchergebnis)
    - [Zusätzl. Datenquellen](#einstellungen-quelle)
    - [Blacklist](#einstellungen-blacklist)
- [Plaintext](#plaintext)
- [Search Highlighter](#search_highlighter)

<a name="wartung"></a>
# Wartung

Um die Suche in Betrieb zu nehmen, sollten zunächst alle gewünschten Einstellungen vorgenommen werden und anschließend der Suchindex eingerichtet werden.

> Tipp: Die hier definierten Sucheinstellungen können auch direkt an der `search_it`-Klasse vorgenommen bzw. überschrieben werden, um mehrere Suchen auf einer Seite umzusetzen.

Aktion | Erläuterung
------ | ------
Index vollständig erstellen | Die Index-Tabelle wird gelöscht und neu aufgebaut.
Index schrittweise erstellen | Führt die Indexierung in mehreren Schritten aus, um die Skriptlaufzeit (max_execution_time) gering zu halten. Artikel und Medien werden einzeln indexiert, Datenbankeinträge in 100er-Schriten.
Suchcache löschen | Wenn eine Neuindexierung nicht erforderlich ist, kann auch ausschließlich der Cache gelöscht werden.
Keyword-Index leeren | Löscht alle Keywords, die bei der Indexierung oder über Suchanfragen gesammelt wurden. Da diese Keywords z. B. für die Ähnlichkeitssuche gebraucht werden, sollten diese nur in Ausnahmefällen gelöscht werden.
Statistik löschen | Setzt die Statistik zurück. Die Anzahl aller gesuchten Begriffe wird auf 0 gesetzt.

<a name="einstellungen"></a>
# Einstellungen

<a name="einstellungen-suchmodus"></a>
## Suchmodus

Dies sind die Standard-Einstellungen für jede Suche. 

> Tipp: In den erweiterten Beispielen wird erklärt, wie das Suchobjekt mit eigenen Parametern überschrieben werden kann. So lassen sich mehrere Suchen in einer Website realisieren, bspw. eine Produktsuche oder eine Mitarbeiter-Suche.

### Suchmodi

#### Logischer Suchmodus

Wenn mehr als ein Begriff in das Suchfeld eingegeben wird, ...

Option | Erläuterung
------ | ------
`Konjunktive Suche (AND)` | ... müssen beide Begriffe im Treffer vorkommen.
`Disjunktive Suche (OR)` | ... genügt einer von beiden Begriffen.

#### Textmodus

Der Textmodus besagt, welche Inhalte für die Suche auf einer Seite verarbeitet werden.

Option | Erläuterung
------ | ------
Durchsuche Text ohne HTML-Tags (Plain) | durchsuche ausschließlich Text (empfohlen)
Durchsuche Text mit HTML-Tags (HTML) | durchsucht auch HTML-Code und Attribute
Durchsuche beides (HTML und Plain) |

> Tipp: In der Datenbank `rex_search_it_index` werden die indexierten Varianten `plaintext` und `unchangedtext` abgelegt.

#### Ähnlichkeitssuche

Bei der Ähnlichkeitssuche werden ähnliche Begriffe dem gesuchten Begriff zugeordnet. Gleichklingende bekommen dabei einen gleichen Code. Beispiele hierfür sind:
* Tippfehler: `Standard` vs. `Standart`
* Verwechslungen: `Maier` vs. `Meyer`

Option | Erläuterung
------ | ------
Deaktivieren | Es werden nur exakte Treffer angezeigt.
Soundex | Gleichklingende Wörter führen zu einem Treffer. Verwendet den [Soundex-Algorithmus](https://de.wikipedia.org/wiki/Soundex). 
Metaphone | Gleichklingende Wörter führen zu einem Treffer. [Metaphone](https://de.wikipedia.org/wiki/Metaphone) eignet sich für englische Begriffe.
Kölner Phonetik | Gleichklingende Wörter führen zu einem Treffer. [Kölner Phonetik](https://de.wikipedia.org/wiki/K%C3%B6lner_Phonetik) eignet sich für deutsche Begriffe.
Alle | Überprüft Soundex, Metaphone und Kölner Phonetik nach Treffern.
Die Ähnlichkeitssuche auch dann durchführen, wenn Ergebnisse vorhanden sind? | Ausschalten, um die Ähnlichkeitssuche nur dann zu aktivieren, wenn kein Suchergebnis gefunden wurde.

#### MySQL-Suchmodus

Option | Erläuterung
------ | ------
LIKE | findet auch Teilwörter, z.B. `Boot` in `Hausboot`, ist jedoch langsamer.
MATCH AGAINST  | findet nur ganze Wörter, ist dafür schneller.

> Tipp: Obwohl die genauere Suche mit MATCH AGAINST weniger Suchergebnisse präsentiert, wird der Einsatz dieser Methode empfohlen, da die Suche dadurch beschleunigt wird. Das Manko der genaueren Suche - wenn man es denn so empfindet - kann über die Ähnlichkeitssuche ausgeglichen werden.

### Indexierung

Bei der Indexierung durchsucht Search it alle in den Einstellungen angegebenen Orte (Artikel, Datenbank, Medienpool) und erstellt einen Suchindex-Cache. 

#### Art und Weise

Legt fest, wie Artikel indexiert werden.

Option | Erläuterung
------ | ------
Indexierung der Artikel über eine HTTP-GET-Anfrage | indexiert Artikel so, als wenn Sie über das Frontend abgerufen werden.
Indexierung der Artikel über den Redaxo-Cache (ohne Template, nur der Artikel) | indexiert den Artikel so, wie er in __todo__ 
Indexierung der Artikel über den Redaxo-Cache (mit Template, liefert das gleiche Ergebnis wie per HTTP-GET-Anfrage) | indexiert die vollständige Seite.
Offline-Artikel indexieren | indexiert auch Artikel, die in der Struktur als `offline` markiert wurden.
Artikel (ADD, EDIT, DELETE) automatisch (de)indexieren | indexiert automatisch neue Artikel, reindexiert bearbeitete Artikel und deindexiert Artikel, die gelöscht wurden.
Extension Point `"OUTPUT_FILTER"` aufrufen | Ruft den OUTPUT_FILTER auf, bspw., wenn das SPROG-Addon benutzt wurde und die Einstellung `Indexierung der Artikel` über den Redaxo-Cache erfolgt. __todo__ ***stimmt das?***

<a name="einstellungen-suchergebnis"></a>
## Suchergebnis

Bei der Ausgabe der Suchergebnisse können Standard-Einstellungen gesetzt werden:

### Erscheinungsbild des Highlight-Texts

Der Highlight-Text zeigt den gefundenen Suchbegriff als Teaser im Kontext. Der gefundene Suchbegriff kann im Highlight-Text ausgezeichnet werden, um ihn optisch zu formatieren, z.B. bei der Suche nach dem Begriff `Geld`:

```
<p>... wie viel <strong class="search_it-keyword">Geld</strong> lässt sich damit verdienen? Erfahren ....</p>
```

Option | Erläuterung
------ | ------
Start-Tag | Tag vor dem gefundenen Suchbegriff, z.B. `<strong class="search_it-keyword">`
End-Tag | Tag nach dem gefunden Suchbegriff, z.B. `</strong>`
Maximale Trefferanzahl | Wenn der gefundene Suchbegriff mehr als 1x im Highlight-Text erscheint: Gibt an, wie oft der Treffer angezeigt wird.
Maximale Zeichenanzahl für Teaser | Anzahl der Zeichen, mit denen das Suchergebnis angeteasert wird.
Maximale Zeichenanzahl um hervorgehobene Suchbegriffe herum | Anazhl der Zeichen, mit denen der gefundene Suchbegriff umgeben wird.

### Hervorhebung

Langer Rede kurzer Sinn: Die Hervorhebung wird bei der Auswahl in einer Vorschau dargestellt und könnte dort nicht besser erklärt werden als hier ;)

Option | Erläuterung
------ | ------
Ab Anfang des Satzes, in dem mindestens einer der Suchbegriffe auftaucht | 
Ab Anfang des Absatzes, in dem mindestens einer der Suchbegriffe auftaucht | 
Alle gefundenen Suchbegriffe werden mit den sie umgebenden Wörtern dargestellt | 
Für jeden gefundenen Suchbegriff wird genau eine Textstelle wiedergegeben | 
Als Teaser, in dem eventuell vorkommende Suchebgriffe hervorgehoben sind | 
Als Array mit allen Suchbegriffen und Textstellen | 
Beispieltext mit Sucheingabe |


<a name="einstellungen-quelle"></a>
## Zusätzliche Datenquellen

Hier werden Datenquellen für die Indexierung zusätzlich zu den Redaxo-Artikeln definiert, z. B. Datenbanktabellen, der Medienpool sowie externe Verzeichnisse.

### Datenbankspalten in die Suche einschließen

Hier können DB-Spalten ausgewählt werden, die auch durchsucht werden sollen. Hierfür bietet sich zusätzliche Addon-Felder an, z. B. `rex_article.yrewrite_description` oder Daten, die über das Addon `yform` erstellt werden.

> Tipp: Die Indexierung sollte neben den gewünschten Inhaltsfeldern auch das `id`-Feld / den Primary Key des Datensatzes indizieren sowie alle Felder, die bei der Ausgabe berücksichtigt werden sollen, bspw. Bilder, Teaser o.ä.

### Dateisuche

Die Dateisuche durchsucht angegebene Dateien nach Begriffen. Bei PDFs, deren Inhalt als Text vorliegt, wird eine Volltextsuche im PDF ermöglicht. 

Option | Erläuterung
------ | ------
Dateiendungen (frei lassen für beliebige Dateien) | Kommagetrennte Angabe von Dateien, die in der Medienpool-Indexierung
Medienpool indexieren | Gibt an, ob die Tabelle `rex_media` zur Medienpool-Suche indexiert wird.
Verzeichnistiefe | Gibt an, bis zu welcher Tiefe Dateien in den ausgewählten Verzeichnissen indexiert werden sollen.
Folgende Ordner in die Suche einschließen | Externe Ordner innerhalb der Redaxo-Installation werden indexiert.
Unterordner auswählen |


<a name="einstellungen-blacklist"></a>
## Blacklist

### Wörter, Kategorien und Artikel von der Suche ausschließen

Schließt Begriffe, Artikel und Kategorien standardmäßig von der Suche aus. 

> Hinweis: Diese Einstellungen betreffen nur die Suchergebnisse und können in der `search_it`-Klasse überschrieben werden. Begriffe, Kategorien und Artikel werden trotzdem bei der Indexierung berücksichtigt. __todo__ ***Stimmt das?***

Option | Erläuterung
------ | ------
Wörter (kommaseperiert) | Begriffe, die von der Suche ausgeschlossen werden.
Artikel | Artikel (`rex_article`-IDs), die von der Suche ausgeschlossen werden.
Kategorien | Kategorien (`rex_category`-IDs), die von der Suche ausgeschlossen werden.

> Tipp: Der Artikel des Suchergebnis sollte von der Suche ausgeschlossen werden.






<a name="plaintext"></a>
# Plaintext

Option | Erläuterung
------ | ------
CSS-Selektoren (komma-separiert) | __todo__
Reguläre Ausdrücke | __todo__
Textile parsen | __todo__
HTML-Tags entfernen | __todo__
Standard-Plaintext-Konvertierung durchführen | __todo__

<a name="search_highlighter"></a>
# Search Highlighter

Option | Erläuterung
------ | ------
Tag um die Suchbegriffe | __todo__
Class | __todo__
inline CSS | __todo__
Stil CSS einbinden | __todo__
Stil (CSS) | __todo__
Eigener Stil | __todo__