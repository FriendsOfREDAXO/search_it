# Wie kann ich sprachabhängig suchen?

Search it sucht per Standard in allen Sprachen. Um sprachabhängige Suchen zu erlauben, muss der `search_it`-Klasse die Sprach-ID der Sprache, in der gesucht werden soll, übergeben werden.

*Such-Formular*

```html
<input type="hidden" name="clang" value="REX_CLANG_ID" />
```

*Suchergebnis-Ausgabe*

```php
$search_it = new search_it(REX_CLANG_ID);
```