# Filtern von Suchergebnissen

In `Search it` ist es derzeit nicht möglich, eigene Filter-Parameter zu definieren.

Es ist jedoch möglich, bereits in der MySQL-Tabelle eine `VIEW` zu erstellen, die nur die gewünschten Datensätze enthält. Diese `VIEW` kann dann von `Search it` in den Einstellungen unter `Zusätzliche Datenquellen` als Tabelle indexiert werden.

Mögliche Szenarien für eine solche View sind:
* Nur Dateien aus dem Medienpool auflisten, die die Meta-Info `öffentlich` enthalten
* Nur Produkte aus einer Produkt-Tabelle, die den status "online" haben
* Suche in mehreren Datenbanktabellen, die über Relationen mit einander verbunden sind.

> **Tipp:** Mit dem Redaxo-AddOn `Adminer` lassen sich die nachfolgenden Schritte direkt aus dem Redaxo-Backend erledigen, ohne sich in `PHPMyAdmin` oder ein anderes DBMS einzuloggen.

## Schritt für Schritt-Anleitung

**1. SQL-Abfrage formulieren**
 
Zunächst formulieren wir eine `SELECT`-Abfrage, die nur die gewünschten Datensätze einer Datenbanktabelle übrig lässt. In diesem Beispiel sollen nur Excel-Dateien aus dem Medienpool gefunden werden.

```
SELECT id, filetype, filename, title
FROM rex_media
WHERE filetype = "application/vnd.ms-excel"
```

Das Ergebnis dieser Tabelle könnte bspw. so aussehen:

```
id  filetype                  filename            title
43  application/vnd.ms-excel	auflistung.xls	    Auflistung aller Aufgaben
44  application/vnd.ms-excel	bestellung.xls      Bestellformular
```

**2. VIEW erstellen**

Aus der SELECT-Abfrage wird eine `VIEW` erstellt. Die `VIEW` ist eine Ergebnistabelle und mit den Datensätzen der Original-Tabellen verknüpft. Eine Änderung in der Original-Tabelle wird sofort in der `VIEW` abgebildet.

Aus dem o.g. Beispiel wird nun in der Datenbank eine `VIEW` namens `rex_media_excel_view` erstellt.

```
CREATE VIEW rex_media_excel_view AS 
SELECT id, filetype, filename, title
FROM rex_media
WHERE filetype = "application/vnd.ms-excel"
```

Die `VIEW` `rex_media_excel_view` ist jetzt permanent eingerichtet und zugriffsbereit für `Search it`

** 3. Search it konfigurieren **

In den `Search it`-Einstellungen des Redaxo-Backends unter `Zusätzliche Datenquellen` kann jetzt `rex_media_excel_view` als Datenquelle angegeben werden. Anschließend muss der Index erneuert werden und ggf. das Suchausgabe-Modul an die Datenbanktabelle angepasst werden, siehe:

* [Aufbau der Suchergebnisse](search_it-result.md)
* [Erweiterte Suche](module-enhanced.md)


