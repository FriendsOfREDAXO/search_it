# Suchergebnisse im Ziel-Artikel hervorheben

Search it kann nicht nur Suchergebnisse innerhalb der Suchergebnis-Liste hervorheben, sondern auch in den betroffnen Artikeln. Dazu muss der `Search Highlighter` in den Search it-Einstellungen aktiviert sein.

Damit die Suche den Suchbegriff an die aufgerufene Seite übergibt, muss der Link in der Suchergebnis-Liste angepasst werden.

```
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

> Hinweis: Im Gegensatz zu RexSearch für Redaxo 4 wird der Begriff nur innerhalb des `<body>`-Tags hervorgehoben, sodass Metadaten nicht umformatiert werden.