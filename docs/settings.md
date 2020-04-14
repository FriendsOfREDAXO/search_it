# Konfiguration

Um die Suche in Betrieb zu nehmen, sollten zunächst alle gewünschten Einstellungen vorgenommen werden und anschließend der Suchindex eingerichtet werden.

> Hinweis: Es werden zu Beginn nur Artikelinhalte ohne Medien, Metainfos (wie bspw. dem Artikelname) oder zusätzlichen Datenbanktabellen indexiert. Um Meta-Infos, Medien und Datenbanktabellen zu indexieren, müssen entsprechende Einstellungen bearbeitet werden.

- [Wartung](#wartung)
- [Einstellungen](#einstellungen)
  - [Index und Suchmodus](#einstellungen-suchmodus)
  - [Suchergebnis](#einstellungen-suchergebnis)
  - [Zusätzliche Datenquellen](#einstellungen-quelle)
  - [Blacklist](#einstellungen-blacklist)
- [Plaintext](#plaintext)
- [Search Highlighter](#search_highlighter)

<a name="wartung"></a>

## Wartung

Aktion | Erläuterung
------ | ------
Index vollständig erstellen | Die Index-Tabelle wird gelöscht und neu aufgebaut.
Index schrittweise erstellen | Führt die Indexierung in mehreren Schritten aus, um die Skriptlaufzeit (max_execution_time) gering zu halten. Artikel und Medien werden einzeln indexiert, Datenbankeinträge in 100er-Schriten.
Suchcache löschen | Wenn eine Neuindexierung nicht erforderlich ist, kann auch ausschließlich der Cache gelöscht werden.
Keyword-Index leeren | Löscht alle Keywords, die bei der Indexierung oder über Suchanfragen gesammelt wurden. Da diese Keywords z. B. für die Ähnlichkeitssuche gebraucht werden, sollten diese nur in Ausnahmefällen gelöscht werden.
Statistik löschen | Setzt die Statistik zurück. Die Anzahl aller gesuchten Begriffe wird auf 0 gesetzt.

<a name="einstellungen"></a>

## Einstellungen

> Tipp: Die hier definierten Sucheinstellungen können auch direkt an der `search_it`-Klasse vorgenommen bzw. überschrieben werden, um mehrere Suchen auf einer Seite umzusetzen.

<a name="einstellungen-suchmodus"></a>

### Index und Suchmodus

Dies sind die Standard-Einstellungen für den Aufbau eines Suchindex und die Durchführung der Suche.

> Tipp: In den erweiterten Beispielen wird erklärt, wie das Suchobjekt mit eigenen Parametern überschrieben werden kann. So lassen sich mehrere Suchen in einer Website umsetzen, bspw. eine Produktsuche oder eine Mitarbeiter-Suche.

#### Indexierung

Bei der Indexierung durchsucht Search it alle in den Einstellungen angegebenen Orte (Artikel, URLs aus dem URL-Addon, Datenbank, Medienpool) und erstellt einen Suchindex.


#### Suchmodi

##### Logischer Suchmodus

Wenn mehr als ein Begriff in das Suchfeld eingegeben wird, ...

Option | Erläuterung
------ | ------
`Konjunktive Suche (AND)` | ... müssen beide Begriffe im Treffer vorkommen.
`Disjunktive Suche (OR)` | ... genügt einer von beiden Begriffen.

##### Textmodus

Der Textmodus besagt, welche Inhalte für die Suche auf einer Seite verarbeitet werden.

Option | Erläuterung
------ | ------
Durchsuche Text ohne HTML-Tags (Plain) | durchsuche ausschließlich Text (empfohlen)
Durchsuche Text mit HTML-Tags (HTML) | durchsucht auch HTML-Code und Attribute
Durchsuche beides (HTML und Plain) |

> Tipp: In der Datenbank `rex_search_it_index` werden die indexierten Varianten `plaintext` und `unchangedtext` abgelegt.

##### Ähnlichkeitssuche

Bei der Ähnlichkeitssuche werden ähnliche Begriffe dem gesuchten Begriff zugeordnet. Gleichklingende bekommen dabei einen gleichen Code. Beispiele hierfür sind:

- Tippfehler: `Standard` vs. `Standart`
- Verwechslungen: `Maier` vs. `Meyer`

Option | Erläuterung
------ | ------
Deaktivieren | Es werden nur exakte Treffer angezeigt.
Soundex | Gleichklingende Wörter führen zu einem Treffer. Verwendet den [Soundex-Algorithmus](https://de.wikipedia.org/wiki/Soundex).
Metaphone | Gleichklingende Wörter führen zu einem Treffer. [Metaphone](https://de.wikipedia.org/wiki/Metaphone) eignet sich für englische Begriffe.
Kölner Phonetik | Gleichklingende Wörter führen zu einem Treffer. [Kölner Phonetik](https://de.wikipedia.org/wiki/K%C3%B6lner_Phonetik) eignet sich für deutsche Begriffe.
Alle | Überprüft Soundex, Metaphone und Kölner Phonetik nach Treffern.
Die Ähnlichkeitssuche auch dann durchführen, wenn Ergebnisse vorhanden sind? | Ausschalten, um die Ähnlichkeitssuche nur dann zu aktivieren, wenn kein Suchergebnis gefunden wurde.

##### MySQL-Suchmodus

Option | Erläuterung
------ | ------
LIKE | findet auch Teilwörter, z.B. `Boot` in `Hausboot`, ist jedoch langsamer.
MATCH AGAINST  | findet nur ganze Wörter, ist dafür schneller.

> Tipp: Obwohl die genauere Suche mit MATCH AGAINST weniger Suchergebnisse präsentiert, wird der Einsatz dieser Methode empfohlen, da die Suche dadurch beschleunigt wird. Das Manko der genaueren Suche - wenn man es denn so empfindet - kann über die Ähnlichkeitssuche ausgeglichen werden.

<a name="einstellungen-suchergebnis"></a>

### Suchergebnis

Bei der Ausgabe der Suchergebnisse können Standard-Einstellungen gesetzt werden:

#### Erscheinungsbild des Highlight-Texts

Der Highlight-Text zeigt den gefundenen Suchbegriff als Teaser im Kontext. Der gefundene Suchbegriff kann im Highlight-Text ausgezeichnet werden, um ihn optisch zu formatieren, z.B. bei der Suche nach dem Begriff `Geld`:

```html
<p>... wie viel <strong class="search_it-keyword">Geld</strong> lässt sich damit verdienen? Erfahren ....</p>
```

Option | Erläuterung
------ | ------
Start-Tag | Tag vor dem gefundenen Suchbegriff, z.B. `<strong class="search_it-keyword">`
End-Tag | Tag nach dem gefunden Suchbegriff, z.B. `</strong>`
Maximale Trefferanzahl | Wenn der gefundene Suchbegriff mehr als 1x im Highlight-Text erscheint: Gibt an, wie oft der Treffer angezeigt wird.
Maximale Zeichenanzahl für Teaser | Anzahl der Zeichen, mit denen das Suchergebnis angeteasert wird.
Maximale Zeichenanzahl um hervorgehobene Suchbegriffe herum | Anazhl der Zeichen, mit denen der gefundene Suchbegriff umgeben wird.

#### Hervorhebung

Markiert den Suchbegriff innerhalb der Suchergebnis-Liste.

Langer Rede kurzer Sinn: Die Hervorhebung wird bei der Auswahl in einer Vorschau dargestellt und könnte dort nicht besser erklärt werden als hier ;)

Option | Erläuterung
------ | ------
Ab Anfang des Satzes, in dem mindestens einer der Suchbegriffe auftaucht | *Siehe Einstellung*
Ab Anfang des Absatzes, in dem mindestens einer der Suchbegriffe auftaucht | *Siehe Einstellung*
Alle gefundenen Suchbegriffe werden mit den sie umgebenden Wörtern dargestellt | *Siehe Einstellung*
Für jeden gefundenen Suchbegriff wird genau eine Textstelle wiedergegeben | *Siehe Einstellung*
Als Teaser, in dem eventuell vorkommende Suchebgriffe hervorgehoben sind | *Siehe Einstellung*
Als Array mit allen Suchbegriffen und Textstellen | *Siehe Einstellung*
Beispieltext mit Sucheingabe | *Siehe Einstellung*

#### Search Highlighter

Markiert den Suchbegriff auf der tatsächlichen Seite zum Suchbegriff mit einem `<span class="">`, wenn auf den Link des Suchergebnis geklickt wurde.

Option | Erläuterung
------ | ------
CSS-Klasse | CSS-Klasse, die das `<span>`-Element tragen soll, bspw. `search_it-hit`

<a name="einstellungen-quelle"></a>

### Zusätzl. Datenquellen

Hier werden Datenquellen für die Indexierung zusätzlich zu den REDAXO-Artikeln definiert, z. B. Datenbanktabellen, der Medienpool sowie externe Verzeichnisse.

#### Datenbankspalten in die Suche einschließen

Hier können DB-Spalten ausgewählt werden, die auch durchsucht werden sollen. Hierfür bietet sich zusätzliche AddOn-Felder an, z. B. `rex_article.yrewrite_description` oder Daten, die über das AddOn `yform` erstellt werden.

> Tipp: Die Indexierung sollte neben den gewünschten Inhaltsfeldern auch das `id`-Feld / den Primary Key des Datensatzes indizieren sowie alle Felder, die bei der Ausgabe berücksichtigt werden sollen, bspw. Bilder, Teaser o.ä.

#### Datei-Inhalte durchsuchen

Die Dateisuche durchsucht angegebene Dateien nach Begriffen. Bei PDFs, deren Inhalt als Text vorliegt, wird eine Volltextsuche im PDF ermöglicht.

Option | Erläuterung
------ | ------
Dateiendungen (frei lassen für beliebige Dateien) | Kommagetrennte Angabe von Dateien, die in der Medienpool-Indexierung, z. B. `txt,csv,pdf`
`/media/`-Dateien indexieren | Gibt an, ob das Verzeichnis `/media/` indexiert werden soll.
Verzeichnistiefe | Gibt an, bis zu welcher Tiefe Dateien in den ausgewählten Verzeichnissen indexiert werden sollen.
Folgende Ordner in die Suche einschließen | Externe Ordner innerhalb der REDAXO-Installation werden indexiert.
Unterordner auswählen |

<a name="blacklist"></a>

### Blacklist

#### Wörter, Kategorien und Artikel von der Suche ausschließen

Schließt Begriffe, Artikel und Kategorien standardmäßig von der Suche aus.

> Hinweis: Diese Einstellungen betreffen nur die Suchergebnisse und können in der `search_it`-Klasse überschrieben werden. Begriffe, Kategorien und Artikel werden trotzdem bei der Indexierung berücksichtigt.

Option | Erläuterung
------ | ------
Wörter (kommaseperiert) | Begriffe, die von der Suche ausgeschlossen werden.
Artikel | Artikel (`rex_article`-IDs), die von der Suche ausgeschlossen werden.
Kategorien | Kategorien (`rex_category`-IDs), die von der Suche ausgeschlossen werden.

> Tipp: Der Artikel des Suchergebnis sollte von der Suche ausgeschlossen werden.

<a name="plaintext"></a>

### Plaintext

> Tipp: Die Reihenfolge der nachfolgenden Optionen lässt sich per Drag & Drop festlegen.

Option | Erläuterung
------ | ------
CSS-Selektoren | Kommagetrennte Liste an Selektoren, deren Inhalte von der Suche auschgeschlossen werden. Bspw. werden mit `div.donotsearch` alle Inhalte der entsprechenden `<div>`-Elemente nicht in den Index übernommen.
Reguläre Ausdrücke | Reguläre Ausdrücke, die im Suchindex ersetzt werden sollen. In jeder ungeraden Zeile wird das Suchmuster festgelegt, in jeder darauffolgenden Zeile das Ersetzungsmuster.
Textile parsen | Führt die Funktion `rex_textile::parse()` aus.
HTML-Tags entfernen | Wendet die Funktion `strip_tags()` auf den Plaintext an.
Standard-Plaintext-Konvertierung durchführen | Führt die Plaintext-Konvertierung von Search it zusätzlich aus.

> Hinweis: Um die Einstellungen des Plaintext-AddOns zu übernehmen, muss die Indexierung erneut ausgeführt werden.
