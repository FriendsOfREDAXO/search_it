# Modul-Eingabe

Dieses Formular kann bspw. in den `<header>` eines Website-Templates eingebunden werden.

**PHP** 

```
<?php
// Artikel ID des Suchmoduls
$article_id = 1;
$article = rex_article::get($article_id);
$request = rex_request('search', 'string', false);
?>

<section class="search_it-search">
	<form class="search_it-form" id="search_it-form1" action="<?php echo $article->getUrl(); ?>" method="get">
		<div class="search_it-flex">
			<?php
				echo '<input type="text" name="search" value="'. ($request ? rex_escape($request) : '') .'" placeholder="Suchbegriff eingeben" />';
			?>
			<button class="search_it-button" type="submit">
				<img src="<?php print rex_url::addonAssets('d2u_helper', 'icon_search.svg'); ?>">
			</button>
		</div>
	</form>
</section>
```

**CSS**

Das Sucheingabe-Formular kann beliebig formatiert und mit Klassen ausgezeichnet werden. Das obige Beispiel ist aus dem kompletten Suchmodul entnommen. Es kann das CSS des Kompletten Suchmoduls verwendet werden.