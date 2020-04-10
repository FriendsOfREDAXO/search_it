# Komplexe Suche

> Hinweis: Dieses Beispiel wurde noch nicht für Search_it portiert. Du kannst helfen, diese Anleitung zu korrigieren: https://github.com/FriendsOfREDAXO/search_it/

Dieses Beispielmodul ähnelt dem Beispiel: Modul zur Suche mit PDF-Dateien, Pagination und Ähnlichkeitssuche. Die Ähnlichkeitssuche sollte aktiviert, sowie bei der Dateisuche die Option "Medienpool indexieren" ausgewählt sein. Außerdem sollte in dem Feld für die Dateiendungen nur "pdf" stehen.

Ein erweitertes Suchformular bietet dem Nutzer an, folgende Punkte auszuwählen:

- Suchmodus (AND oder OR)
- Suchen in (Kategorieauswahl)
- Wieviele Ergebnisse pro Seite?

        <form id="search-form" method="get" action="<?php echo rex_geturl(REX_ARTICLE_ID, REX_CLANG_ID, array(), '&'); ?>">
       
          <fieldset>
            <legend>Suchformular</legend>
       
            <input type="hidden" name="article_id" value="REX_ARTICLE_ID" />
            <input type="hidden" name="clang" value="REX_CLANG_ID" />
       
            <p><label for="searchterm">Suchbegriff:</label>
            <input type="text" id="searchterm" name="searchterm" value="<?php echo rex_escape(rex_request('searchterm', 'string', '')); ?>" /></p>
       
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

In dem Modul zur Präsentation der Suchergebnisse werden die entsprechenden Einstellungen an Search it übergeben, die Suche ausgeführt und letztendlich die Suchergebnisse ausgegeben.

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
                    $pdf = rex_media::get($filename[count($filename)-1]);
       
                    echo '    <li class="pdf">
              <h4><a href="'.rex_escape($pdf->getFullPath()).'">'.$pdf->getOrgFileName().'</a></h4>
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
                    $article = rex_article::get($hit['fid']);
       
                    echo '    <li>
              <h4><a href="'.rex_escape($article->getUrl()).'">'.$article->getName().'</a></h4>
              <p class="highlightedtext">'.$text.'</p>
              <p class="url">'.rex::getServer().rex_getUrl($hit['fid'], $hit['clang']).'</p>
            </li>';
                  break;
       
                }
              }
              echo '</ul>';
       
              // Pagination
              if($result['count'] > $showmax){
                $self = rex_article::get(REX_ARTICLE_ID);
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
