# Weitere RexSearch-Beispiel-Module

> Hinweis: Der nachfolgende Modul-Code wurde noch nicht an Search it für REDAXO 5 angpasst.

## Suche mit Pagination

Für umfangreiche Webauftritte kann eine Pagination für die Suchergebnisse sinnvoll oder notwendig sein.

Diese Beispielmodul benötigt die DB-Spalten id, name, art_description und art_keywords aus der Tabelle rex_article.

Über die Konstante SHOWMAX kann die maximale Anzahl an Treffern, die auf der Seite angezeigt werden sollen, eingestellt werden.

```
        <?php
        
        define('SHOWMAX',10);
        
        if(!empty(rex_request('searchit', 'string'))){
            $search_it = new search_it();
            $search_it->setLimit(array($start = isset(rex_get('start', 'int', 0))? intval(rex_get('start', 'int', 0)):0, SHOWMAX));
            $search_it->setSearchAllArticlesAnyway(true);
            $search_it->searchInDbColumn(rex::getTablePrefix().'article', 'name');
            $search_it->searchInDbColumn(rex::getTablePrefix().'article', 'art_description');
            $search_it->searchInDbColumn(rex::getTablePrefix().'article', 'art_keywords');
            
            $result = $search_it->search(rex_request('searchit', 'string'));
            if(count($result['simwords']) > 0){
              $newsearchString = $result['simwordsnewsearch'];
              $result = $search_it->search($newsearchString);
              if($result['count'] > 0)
                echo '<p>Meinten Sie <strong>'.$newsearchString.'</strong>?</p>';
            }
            
            if($result['count'] > 0){
              echo '<ul class="searchresults">';
              foreach($result['hits'] as $hit){
                if($hit['type'] == 'db_column'){
                  $text = $hit['article_teaser'];
                  if($hit['table'] == rex::getTablePrefix().'article')
                    $hit['fid'] = $hit['values']['id'];
                } else {
                  $text = $hit['highlightedtext'];
                }
            
                $article = OOArticle::getArticleById($hit['fid']);
            
                echo '<li>
            <h4><a href="'.($url = htmlspecialchars($article->getUrl())).'">'.$article->getName().'</a></h4>
              <p class="highlightedtext">'.$text.'</p>
              <p class="url">'.rex::getServer().rex_getUrl($hit['fid'], $hit['clang']).'</p></li>';
              }
              echo '</ul>';
            
              // Pagination
              if($result['count'] > SHOWMAX){
                $self = OOArticle::getArticleById(REX_ARTICLE_ID);
                echo '<ul class="pagination">';
                for($i = 0; ($i*SHOWMAX) < $result['count']; $i++){
                  if(($i*SHOWMAX) == $start){
                    echo '<li>'.($i+1).'</li>';
                  } else {
                    echo '<li><a href="'.$self->getUrl(array('search_it' => rex_request('searchit', 'string'), 'start' => $i*SHOWMAX)).'">'.($i+1).'</a></li>';
                  }
                }
                echo '</ul>';
              }
            }
        }
        
        ?>
```

## Ähnlichkeitssuche

Dieses Beispielmodul erweitert das Paginationsmodul um eine Suche nach ähnlichen Wörtern. Wichtig ist dabei, dass die Ähnlichkeitssuche im Backend aktiviert ist.
```
        <?php
        
          define('SHOWMAX',10);
        
          if(!empty(rex_request('searchit', 'string'))){
            $search_it = new search_it();
            $search_it->setLimit(array($start = isset(rex_get('start', 'int', 0))?intval(rex_get('start', 'int', 0)):0, SHOWMAX));
        
            $result = $search_it->search(rex_request('searchit', 'string'));
            if(!$result['count'] AND count($result['simwords']) > 0){
              $newsearchString = $result['simwordsnewsearch'];
              $result = $search_it->search($newsearchString);
              if($result['count'] > 0){
                echo '<p>Meinten Sie <strong>'.$newsearchString.'</strong>?</p>';
              }
            }
        
            if($result['count'] > 0){
              echo '<ul class="searchresults">';
              foreach($result['hits'] as $hit){
                if($hit['type'] == 'db_column'){
                  $text = $hit['article_teaser'];
                  if($hit['table'] == rex::getTablePrefix().'article'){
                    $hit['fid'] = $hit['values']['id'];
                  }
                } else {
                  $text = $hit['highlightedtext'];
                }
        
                $article = OOArticle::getArticleById($hit['fid']);
        
                echo '<li>
            <h4><a href="'.($url = htmlspecialchars($article->getUrl())).'">'.$article->getName().'</a></h4>
              <p class="highlightedtext">'.$text.'</p>
              <p class="url">'.rex::getServer().rex_getUrl($hit['fid'], $hit['clang']).'</p></li>';
              }
              echo '</ul>';
        
              // Pagination
              if($result['count'] > SHOWMAX){
                $self = OOArticle::getArticleById(REX_ARTICLE_ID);
                echo '<ul class="pagination">';
                for($i = 0; ($i*SHOWMAX) < $result['count']; $i++){
                  if(($i*SHOWMAX) == $start){
                    echo '<li>'.($i+1).'</li>';
                  } else {
                    echo '<li><a href="'.$self->getUrl(array('search_it' => rex_request('searchit', 'string'), 'start' => $i*SHOWMAX)).'">'.($i+1).'</a></li>';
                  }
                }
                echo '</ul>';
              }
            } else {
              echo '<em>Leider nichts gefunden.</em>';
            }
          }
        
        ?>
```
## Suche mit PDF-Dateien, Pagination und Ähnlichkeitssuche

Dieses Beispielmodul erweitert das Paginationsmodul und die Ähnlichkeitssuche um die Suche von PDF-Dateien aus dem Medienpool. Die Ähnlichkeitssuche sollte aktiviert, sowie bei der Dateisuche die Option "Medienpool indexieren" ausgewählt sein. Außerdem sollte in dem Feld für die Dateiendungen nur "pdf" stehen.

```
        <?php
        
          define('SHOWMAX',10);
        
          if(!empty(rex_request('searchit', 'string'))){
            $search_it = new search_it();
            $search_it->setLimit(array($start = isset(rex_get('start', 'int', 0))?intval(rex_get('start', 'int', 0)):0, SHOWMAX));
        
            $result = $search_it->search(rex_request('searchit', 'string'));
            if(!$result['count'] AND count($result['simwords']) > 0){
              $newsearchString = $result['simwordsnewsearch'];
              $result = $search_it->search($newsearchString);
              if($result['count'] > 0){
                echo '<p>Meinten Sie <strong>'.$newsearchString.'</strong>?</p>';
              }
            }
        
            if($result['count'] > 0){
              echo '<ul class="searchresults">';
              foreach($result['hits'] as $hit){
                if($hit['type'] == 'db_column'){
                  $text = $hit['article_teaser'];
                  if($hit['table'] == rex::getTablePrefix().'article')
                    $hit['fid'] = $hit['values']['id'];
                } else {
                  $text = $hit['highlightedtext'];
                }
        
                if($hit['type'] == 'file' AND $hit['fileext'] == 'pdf'){
                  // PDF-Datei
                  $filename = explode('/', $hit['filename']);
                  $pdf = OOMedia::getMediaByFileName($filename[count($filename)-1]);
        
                  echo '    <li class="pdf">
              <h4><a href="'.htmlspecialchars($pdf->getFullPath()).'">'.$pdf->getOrgFileName().'</a></h4>
              <p class="highlightedtext">'.$text.'</p>
              <p class="url">'.rex::getServer().'files/'.$pdf->getOrgFileName().'</p>
            </li>';
                } else {
                  // Artikel oder DB-Spalte aus der Artikel-Tabelle
                  $article = OOArticle::getArticleById($hit['fid']);
        
                  echo '    <li>
              <h4><a href="'.htmlspecialchars($article->getUrl()).'">'.$article->getName().'</a></h4>
              <p class="highlightedtext">'.$text.'</p>
              <p class="url">'.rex::getServer().rex_getUrl($hit['fid'], $hit['clang']).'</p>
            </li>';
                }
              }
              echo '</ul>';
        
              // Pagination
              if($result['count'] > SHOWMAX){
                $self = OOArticle::getArticleById(REX_ARTICLE_ID);
                echo '<ul class="pagination">';
                for($i = 0; ($i*SHOWMAX) < $result['count']; $i++){
                  if(($i*SHOWMAX) == $start){
                    echo '<li>'.($i+1).'</li>';
                  } else {
                    echo '<li><a href="'.$self->getUrl(array('search_it' => rex_request('searchit', 'string'), 'start' => $i*SHOWMAX)).'">'.($i+1).'</a></li>';
                  }
                }
                echo '</ul>';
              }
            } else {
              echo '<em>Leider nichts gefunden.</em>';
            }
          }
        
        ?>
```

##Komplexe Suche

Dieses Beispielmodul ähnelt dem Beispiel: Modul zur Suche mit PDF-Dateien, Pagination und Ähnlichkeitssuche. Die Ähnlichkeitssuche sollte aktiviert, sowie bei der Dateisuche die Option "Medienpool indexieren" ausgewählt sein. Außerdem sollte in dem Feld für die Dateiendungen nur "pdf" stehen.

Ein erweitertes Suchformular bietet dem Nutzer an, folgende Punkte auszuwählen:

- Suchmodus (AND oder OR)
- Suchen in (Kategorieauswahl)
- Wieviele Ergebnisse pro Seite? 

Wichtig: Dieses Modul ist nur für Search it ab Version 0.5.
```
        <form id="search-form" method="get" action="<?php echo rex_geturl(REX_ARTICLE_ID, REX_CLANG_ID, array(), '&'); ?>">
        
          <fieldset>
            <legend>Suchformular</legend>
        
            <input type="hidden" name="article_id" value="REX_ARTICLE_ID" />
            <input type="hidden" name="clang" value="REX_CLANG_ID" />
        
            <p><label for="searchterm">Suchbegriff:</label>
            <input type="text" id="searchterm" name="searchterm" value="<?php echo htmlspecialchars(rex_request('searchterm', 'string', '')); ?>" /></p>
        
            <p><label for="logicalmode">Suchmodus:</label>
            <select id="logicalmode" name="logicalmode">
              <option value="and"<?php if(rex_request('logicalmode', 'string', 'and') == 'and') echo ' selected="selected"'; ?>>Suchergebnis muss alle Wörter enthalten</option>
              <option value="or"<?php if(rex_request('logicalmode', 'string', 'and') == 'or') echo ' selected="selected"'; ?>>Suchergebnis muss mindestens ein Wort enthalten</option>
            </select></p>
        
            <p><label for="searchin">Suchen in:</label>
        
        <?php $cat_select = new rex_category_select(true, REX_CLANG_ID, false, false); $cat_select->setAttribute('id', 'searchin'); $cat_select->setAttribute('name', 'searchin[]'); $cat_select->setAttribute('multiple', 'multiple'); $cat_select->setAttribute('size', '10'); $cat_select->setSelected(rex_request('searchin', 'array', array())); $cat_select->show(); ?></p>
        
            <p><input type="checkbox" value="1" name="subcats" id="subcats"<?php if(rex_request('subcats', 'int', 0)) echo ' checked="checked"'; ?> /><label for="subcats">Unterkategorien in die Suche einschließen</label></p>
        
            <p><label for="resultcount">Ergebnisse pro Seite:</label>
            <select id="resultcount" name="resultcount">
        
        <?php $resultcount = rex_request('resultcount', 'int', 10); foreach(array(10,20,50,100) as $option)
        
          echo '    <option value="'.$option.'"'.($resultcount==$option?' selected="selected"':'').'>'.$option.'</option>'."\n";
        
        ?>
        
            </select></p>
        
            <p><input type="submit" id="submit" value="Suche starten" /></p>
          </fieldset>
        
        </form>
```
In dem Modul zur Präsentation der Suchergebnisse werden die entsprechenden Einstellungen an Search it übergeben, die Suche ausgeführt und letztendlich die Suchergebnisse ausgegeben.
```
        <?php
        
          $searchterm = rex_request('searchterm', 'string', '');
          $logicalmode = rex_request('logicalmode', 'string', 'and');
          $showmax = rex_request('resultcount', 'int', 10);
          $searchinIDs = rex_request('searchin', 'array', array());
          $traverseSubcats = rex_request('subcats', 'bool', false);
        
          if(!empty($searchterm)){
            $search_it = new search_it();
            $search_it->setLimit(array($start = rex_get('start', 'int', 0), $showmax));
            $search_it->setLogicalMode($logicalmode);
            if($traverseSubcats){
              $search_it->searchInCategories(a587_getCategories(true, true, $searchinIDs));
            } else {
              $search_it->searchInCategories($searchinIDs);
            }
        
            $result = $search_it->search($searchterm);
            if(!$result['count'] AND count($result['simwords']) > 0){
              $newsearchString = $result['simwordsnewsearch'];
              $result = $search_it->search($newsearchString);
              if($result['count'] > 0){
                echo '<p>Meinten Sie <strong>'.$newsearchString.'</strong>?</p>';
              }
            }
        
            if($result['count'] > 0){
              echo '<ul class="searchresults">';
              foreach($result['hits'] as $hit){
                switch($hit['type']){
                  case 'file':
                    $text = $hit['highlightedtext'];
        
                    // PDF-Datei
                    $filename = explode('/', $hit['filename']);
                    $pdf = OOMedia::getMediaByFileName($filename[count($filename)-1]);
        
                    echo '    <li class="pdf">
              <h4><a href="'.htmlspecialchars($pdf->getFullPath()).'">'.$pdf->getOrgFileName().'</a></h4>
              <p class="highlightedtext">'.$text.'</p>
              <p class="url">'.rex::getServer().'files/'.$pdf->getOrgFileName().'</p>
            </li>';
                  break;
        
                  case 'db_column':
                  case 'article':
                    if($hit['type'] == 'db_column'){
                      $text = $hit['article_teaser'];
                      if($hit['table'] == rex::getTablePrefix().'article')
                        $hit['fid'] = $hit['values']['id'];
                    } else {
                      $text = $hit['highlightedtext'];
                    }
        
                    // Artikel oder DB-Spalte aus der Artikel-Tabelle
                    $article = OOArticle::getArticleById($hit['fid']);
        
                    echo '    <li>
              <h4><a href="'.htmlspecialchars($article->getUrl()).'">'.$article->getName().'</a></h4>
              <p class="highlightedtext">'.$text.'</p>
              <p class="url">'.rex::getServer().rex_getUrl($hit['fid'], $hit['clang']).'</p>
            </li>';
                  break;
        
                }
              }
              echo '</ul>';
        
              // Pagination
              if($result['count'] > $showmax){
                $self = OOArticle::getArticleById(REX_ARTICLE_ID);
                echo '<ul class="pagination">';
                for($i = 0; ($i*$showmax) < $result['count']; $i++){
                  if(($i*$showmax) == $start){
                    echo '<li>'.($i+1).'</li>';
                  } else {
                    echo '<li><a href="'.$self->getUrl(array('search_it' => rex_request('searchit', 'string'), 'start' => $i*$showmax)).'">'.($i+1).'</a></li>';
                  }
                }
                echo '</ul>';
              }
            } else {
              echo '<em>Leider nichts gefunden.</em>';
            }
          }
        
        ?>
```