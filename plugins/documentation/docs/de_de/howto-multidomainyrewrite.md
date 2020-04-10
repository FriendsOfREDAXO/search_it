# Wie funktioniert Search it in Kombination mit YRewrite mit verschiedenen Domains?

Mit YRewrite können verschiedene Domains in einem System vereint werden. Search it sucht per Standard in allen Domains. Um die Suche auf eine bestimmte Domain zu begrenzen kann im Ausgabemodul die Funktion `searchInCategoryTree()` verwendet werden. Das ganze sieht dann so aus:

*Beispiel*

```php
$search_it = new search_it()
$search_it->searchInCategoryTree(rex_yrewrite::getCurrentDomain()->getMountId());
$search_it->search([Suchbegriff]);
```
