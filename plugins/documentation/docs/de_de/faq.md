# Tipps und Tricks

## Mehrsprachigkeit

Search it sucht per Standard in allen Sprachen. Um sprachabhängige Suchen zu erlauben, muss der `search_it`-Klasse die Sprach-ID der Sprache, in der gesucht werden soll, übergeben werden.

**Such-Formular**

```html
<input type="hidden" name="clang" value="REX_CLANG_ID" />
```

**Suchergebnis-Ausgabe**

```php
$search_it = new search_it(REX_CLANG_ID);
```

## Filtern und Sortieren von Suchergebnissen

Das Sortieren von Suchergebnissen ist derzeit noch nicht möglich. Ein passender PR auf GitHub ist jedoch willkommen! Allerdings lassen sich mit ein paar Kniffen dennoch bestimmte Inhalte und Daten von der Indexierung ausschließen.

### Nur bestimmte Kategorien durchsuchen

Der `search_it`-Klasse kann mitgeteilt werden, auf welche Kategorien und Artikel die Ausgabe der Suchergebnisse beschränkt wird. Diese Artikel und Kategorien müssen zuvor indexiert worden sein.

```php
$search_it = new search_it();
$search_it->searchInCategories(array(5,6,13));
$result = $search_it->search(rex_request('search', 'string'));
```

### Mehrsprachigkeit mit YRewrite

> Wie funktioniert Search it in Kombination mit YRewrite mit verschiedenen Domains?

Mit YRewrite können verschiedene Domains in einem System vereint werden. Search it sucht per Standard in allen Domains. Um die Suche auf eine bestimmte Domain zu begrenzen kann im Ausgabemodul die Funktion `searchInCategoryTree()` verwendet werden. Das ganze sieht dann so aus:

```php
$search_it = new search_it()
$search_it->searchInCategoryTree(rex_yrewrite::getCurrentDomain()->getMountId());
$search_it->search([Suchbegriff]);
```

### Datensätze in Datenbanktabellen filtern


In `Search it` ist es derzeit nicht möglich, eigene Filter-Parameter zu definieren.

Es ist jedoch möglich, bereits in der MySQL-Tabelle eine `VIEW` zu erstellen, die nur die gewünschten Datensätze enthält. Diese `VIEW` kann dann von `Search it` in den Einstellungen unter `Zusätzliche Datenquellen` als Tabelle indexiert werden.

Mögliche Szenarien für eine solche View sind:

* Nur Dateien aus dem Medienpool auflisten, die die Meta-Info `öffentlich` enthalten
* Nur Produkte aus einer Produkt-Tabelle, die den status "online" haben
* Suche in mehreren Datenbanktabellen, die über Relationen mit einander verbunden sind.

> **Tipp:** Mit dem REDAXO-AddOn `Adminer` lassen sich die nachfolgenden Schritte direkt aus dem REDAXO-Backend erledigen, ohne sich in `PHPMyAdmin` oder ein anderes DBMS einzuloggen.

#### 1. SQL-Abfrage formulieren

Zunächst formulieren wir eine `SELECT`-Abfrage, die nur die gewünschten Datensätze einer Datenbanktabelle übrig lässt. In diesem Beispiel sollen nur Excel-Dateien aus dem Medienpool gefunden werden.

```sql
SELECT id, filetype, filename, title
FROM rex_media
WHERE filetype = "application/vnd.ms-excel"
```

Das Ergebnis dieser Tabelle könnte bspw. so aussehen:

```text
id  filetype                  filename            title
43  application/vnd.ms-excel	auflistung.xls	    Auflistung aller Aufgaben
44  application/vnd.ms-excel	bestellung.xls      Bestellformular
```

#### 2. VIEW erstellen

Aus der SELECT-Abfrage wird eine `VIEW` erstellt. Die `VIEW` ist eine Ergebnistabelle und mit den Datensätzen der Original-Tabellen verknüpft. Eine Änderung in der Original-Tabelle wird sofort in der `VIEW` abgebildet.

Aus dem o.g. Beispiel wird nun in der Datenbank eine `VIEW` namens `rex_media_excel_view` erstellt.

```sql
CREATE VIEW rex_media_excel_view AS
SELECT id, filetype, filename, title
FROM rex_media
WHERE filetype = "application/vnd.ms-excel"
```

Die `VIEW` `rex_media_excel_view` ist jetzt permanent eingerichtet und zugriffsbereit für `Search it`

#### 3. Search it konfigurieren

In den `Search it`-Einstellungen des REDAXO-Backends unter `Zusätzliche Datenquellen` kann jetzt `rex_media_excel_view` als Datenquelle angegeben werden. Anschließend muss der Index erneuert werden und ggf. das Suchausgabe-Modul an die Datenbanktabelle angepasst werden, siehe:

* [Aufbau der Suchergebnisse](search_it-result.md)
* [Erweiterte Suche](module-enhanced.md)


### Module, Blöcke, Artikel oder bestimmte Abschnitte filtern

Das Plaintext-Plugin hat die Möglichkeit, anhand bestimmter Selektoren Inhalte auszuschließen. So werden bspw. `<header>` oder `<footer>` nicht indexiert. Mit diesem Trick können auch ganze Module von der Suche ausschließen.

```html
<section class="donotsearch">REX_VALUE[1]</section>
```

**Beispiel Plaintext-Selektor:** `section.donotsearch`

Auf dieselbe Weise lassen sich auch Artikel oder Kategorien von der Indexierung ausschließen, indem eine passende Klasse dem `<body>`-Tag zugewiesen wird.

## Suchergebnisse hervorheben

Search it kann nicht nur Suchergebnisse innerhalb der Suchergebnis-Liste hervorheben, sondern auch in den betroffnen Artikeln. Dazu muss der `Search Highlighter` in den Einstellungen aktiviert sein.

Damit die Suche den Suchbegriff an die aufgerufene Seite übergibt, muss der Link in der Suchergebnis-Liste angepasst werden.

```php
if($hit['type'] == 'article') {
    $article = rex_article::get($hit['fid']);
    $url = rex_getUrl(
        $hit['fid'],
        $hit['clang'],
        array('search_highlighter' => rex_request('search', 'string'))
    )
    echo '<a href="'.$url.'" title="'.$article->getName().'">'.$article->getName().'</a>';
}
```

Dadruch wird der Parameter search_highlighter an die Seite übergeben und kann dort ausgelesen werden.