# Aufbau der Suchergebnisse

- [Beispiel](#beispiel)
- [Aufbau](#aufbau)
  - [hits](#hits)
  - [keywords](#keywords)
  - [searchterm](#searchterm)
  - [sql](#sql)
  - [blacklisted](#blacklisted)
  - [time](#time)
  - [count](#count)
  - [hash](#hash)
  - [simwordsnewsearch](#simwordsnewsearch)
  - [simwords](#simwords)

## Beispiel

Mit jeder Suche gibt `search_it`-Klasse ein Ergebnis-Array mit Suchtreffern zurück.

```text
Array
(
    [simwordsnewsearch] => Pudding Puddingpulver Putensteak Putens Milch
    [simwords] => Array
        (
            [Pudding] => Array
                (
                    [keyword] => Pudding Puddingpulver Putensteak Putens
                    [typedin] => Pudding
                    [count] => 67
                )

        )

    [errormessages] =>
    [count] => 2
    [hits] => Array
    [...]
    [blacklisted] =>
    [hash] => 4f6d834a6bf25ad92d60098de19f9ea6
    [time] => 0.42217803001404
)
```

## Aufbau


### hits

Ein Array der Treffer, wobei jeder Treffer selbst ein Array mit folgendem Inhalt ist:

Key | Value
------ | ------
id|die ID des Suchergebnis in der Tabelle `rex_search_it_index`
fid|die Fremd-ID, von dem Datensatz, der indexiert wurde (z. B. die Artikel-ID)
table|Datenbank-Tabelle, von der indexiert wurde
column|das Datenbank-Feld, von der indexiert wurde (NULL, wenn es ein indexierter Artikel ist)
type|`db_column`, `article` oder `file`
clang|Sprach-ID
fileext|Dateiendung (bei Dateisuchen)
filename|Dateiname (ggf. mit Pfad)
unchangedtext|der unveränderte Originaltext
plaintext|der von HTML- und PHP-Tags befreite Text
teaser|Teaser (Plaintext gekürzt auf die Anzahl der maximalen Teaserzeichen)
highlightedtext|Text oder Array mit hervorgehobenen Suchbegriffen innerhalb von Textstellen
article_teaser|wenn eine Datenbankspalte aus der Tabelle `rex_article` indexiert wurde, dann steht hier der Teaser des Artikels
values|wenn mehrere Spalten der gleichen Tabelle indexiert sind, so stehen deren Werte für jeden Datensatz als Array zur Verfügung.

### keywords

Array mit verwendeten Suchbegriffen, z. B.:

```text
[0] => Array
    (
        [search] => Pudding
        [weight] => 1
        [clang] =>
    )

[1] => Array
    (
        [search] => Milch
        [weight] => 1
        [clang] =>
    )
```

### searchterm

Der vom Nutzer eingegebene Suchbegriff, z. B.:  `Pudding Milch`

### sql

Der von Search it erstellte SQL-Befehl, z. B.:

```sql
SELECT SQL_CALC_FOUND_ROWS (SELECT SUM((( MATCH (`plaintext`) AGAINST ('Pudding')) * 1) + (( MATCH (`plaintext`) AGAINST ('Milch')) * 1) + 1) FROM `rex_search_it_index` summe WHERE summe.fid = r1.fid AND summe.ftable = r1.ftable) AS RELEVANCE_SEARCH_IT,
(SELECT COUNT(*) FROM `rex_search_it_index` summe WHERE summe.fid = r1.fid AND (summe.ftable IS NULL OR summe.ftable = r1.ftable) AND (summe.fcolumn IS NULL OR summe.fcolumn = r1.fcolumn) AND summe.texttype = r1.texttype) AS COUNT_SEARCH_IT, `id`,`fid`,`catid`,`ftable`,`fcolumn`,`texttype`,`clang`,`unchangedtext`,`plaintext`,`teaser`,`values`,`filename`,`fileext` FROM `rex_search_it_index` r1
WHERE ((((`plaintext` LIKE '%Pudding%')) AND ((`plaintext` LIKE '%Milch%')))) AND (
    (
    ((( MATCH (`plaintext`) AGAINST ('Pudding')) * 1) + (( MATCH (`plaintext`) AGAINST ('Milch')) * 1) + 1) = (SELECT MAX((( MATCH (`plaintext`) AGAINST ('Pudding')) * 1) + (( MATCH (`plaintext`) AGAINST ('Milch')) * 1) + 1) FROM `rex_search_it_index` r2 WHERE r1.ftable = r2.ftable AND r1.fid = r2.fid )
    AND fid IS NOT NULL
    ) OR
    ftable IS NULL
)
GROUP BY ftable,fid,clang
ORDER BY RELEVANCE_SEARCH_IT DESC
LIMIT 0,5
```

### blacklisted

Array mit "schwarzen" Wörtern, die in der Suchabfrage genutzt wurden

### time

Dauer der Suche

### count

Anzahl aller Datensätze, die ohne LIMIT-Klausel gefunden werden könnten

### hash

Hash, unter dem die Suche im Cache gespeichert wurde

### simwordsnewsearch

Vorschlag für ähnliche Suchbegriffe (nur, wenn die Suche kein Ergebnis brachte)

### simwords:

ein Array mit Wörtern, die den Suchbegriffen ähneln, wobei die Schlüssel dieses Arrays die eingegebenen "falschen" Wörter sind und die Werte wiederum ein Array, das wie folgt aufgebaut ist:

Key | Value
------ | ------
typedin|noch einmal das "falsche" Wort
keyword|das "richtige" Schlüsselwort
count|Anzahl, wie oft das "richtige" Schlüsselwort gefunden wurde