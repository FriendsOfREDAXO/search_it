# Modul-Eingabe

Dieses Formular kann bspw. in den `<header>` eines Website-Templates eingebunden werden.

## Beispiel-PHP

```php
<?php $article_id = 1 // ID des Suchergebnis-Artikels - bitte anpassen! ?>

<form class="search_it-form" id="search_it-form1" action="<?php echo rex_getUrl($article_id, rex_clang::getCurrentId()); ?>" method="get">
    <fieldset>
        <legend>{{ Suche }}</legend>
        <div class="search_it-flex">
            <input type="hidden" name="article_id" value="<?php echo $article_id; ?>" />
            <input type="hidden" name="clang" value="<?php echo rex_clang::getCurrentId(); ?>" />
            <label for="search">{{ Suchbegriff }}</label>
            <input type="text" name="search" value="<?php if(!empty(rex_request('search','string'))) { echo rex_escape(rex_request('search','string')); } ?>" placeholder="{{ Suchbegriff eingeben }}" />
            <input class="search_it-button" type="submit" value="{{ Suchen }}" />
        </div>
    </fieldset>
</form>
```

> Tipp: Bei mehrsprachigen Websites können die Platzhalter `{{ }}` mit dem Sprog-AddOn übersetzt werden.

## Beispiel-CSS

Das Sucheingabe-Formular kann beliebig formatiert und mit Klassen ausgezeichnet werden. Das nachfolgende CSS formatiert das oben vorgegebene Beispiel und blendet den Platzhalter-Text beim Klick in das Such-Eingabefeld aus.

```css
<style>
    .search_it-form {
        box-sizing: border-box;
        font-size: 1rem;
        font-family: sans-serif;
        max-width: 640px;
        padding: 1rem;
        margin: 0 auto;
        border: 1px solid rgba(0,0,0,0.2);
        display: block;
    }
    .search_it-form fieldset {
        display: flex;
        padding: 0;
        margin: 0;
        border: 0;
    }
    .search_it-flex {
        display: flex;
        padding: 0;
        margin: 0;
        border: 0;
    }
    .search_it-form legend, label {
        display: none;  
    }
    .search_it-flex > * {
        flex: 2 2 200px;  
    }
    .search_it-flex > .search_it-button {
        flex: 1 1 100px;  
    }

    .search_it-form input:focus::-webkit-input-placeholder{
      color: transparent;
    }
    .search_it-form input:focus::-moz-placeholder {
      color: transparent;
    }
    .search_it-form input:focus:-ms-input-placeholder {
      color: transparent;
    }
    .search_it-form input:focus:-moz-placeholder {
      color: transparent;
    }
</style>
```
