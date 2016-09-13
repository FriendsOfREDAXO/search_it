<?php

function search_it_getArticles($cats = false) {
    $si = rex_addon::get('search_it');
  
    $whereCats = array();
    if(is_array($cats)){
        foreach($cats as $catID) {
            $whereCats[] = "path LIKE '%|" . $catID . "|%'";
        }
    }
  
    $return = array();
    $query = 'SELECT id,name,path FROM '.rex::getTable('article').' WHERE 1';
    if( !$si->getConfig('indexoffline') ) {
      $query .= ' AND status = 1';
    }
    if(!empty($whereCats)) {
      $query .= ' AND (' . implode(' OR ', $whereCats) . ' OR (id IN (' . implode(',', $cats) . ')))';
    }
    $query .= ' GROUP BY id ORDER BY id';

    $sql = rex_sql::factory();
    foreach($sql->getArray($query) as $art){
        $return[$art['id']] = $art['name'];
    }

    return $return;
}


function search_it_getCategories($_ignoreoffline = true, $_onlyIDs = false, $_cats = false) {
    $si = rex_addon::get('search_it');
  
    $return = array();

    if(!empty($_cats)){
        $whereCats = array();
        $sqlCats = array();
        if(is_array($_cats)){
          foreach($_cats as $catID){
            $whereCats[] = "path LIKE '%|".intval($catID)."|%'";
            $sqlCats[] = intval($catID);
          }
        }

        $return = array();
        $query = 'SELECT id,catname,path FROM '.rex::getTable('article').' WHERE startarticle = 1';
        if( !$si->getConfig('indexoffline') AND $_ignoreoffline ) {
            $query .= ' AND status = 1';
        }
        if(!empty($whereCats)) {
            $query .= ' AND (' . implode(' OR ', $whereCats) . ' OR (id IN (' . implode(',', $sqlCats) . ')))';
        }
        $query .= ' GROUP BY id ORDER BY id';

        $sql = rex_sql::factory();
        foreach($sql->getArray($query) as $cat){
            if($_onlyIDs) {
              $return[] = $cat['id'];
            } else {
              $return[$cat['id']] = $cat['catname'];
            }
        }

    } else {
        $query = 'SELECT id,parent_id,catname,path FROM '.rex::getTable('article') .' WHERE startarticle = 1 AND parent_id=%d';
        if( !$si->getConfig('indexoffline') AND $_ignoreoffline) {
            $query .= ' AND status = 1';
        }
        $query .= ' GROUP BY id ORDER BY catpriority,id';

        $sql = rex_sql::factory();
        $cats = $sql->getArray(sprintf($query,0));

        while(!empty($cats)){
            $cat = array_shift($cats);
            if($_onlyIDs) {
                $return[] = $cat['id'];
            } else {
                $return[$cat['id']] = str_repeat('&nbsp;', substr_count($cat['path'], '|') * 2 - 2) . $cat['catname'];
            }

            array_splice($cats, 0, 0, $sql->getArray(sprintf($query,$cat['id'])));
        }
    }

    return $return;
}


function search_it_getDirs($_startDir = '', $_getSubdirs = false){
    $si = rex_addon::get('search_it');

    $startDepth = substr_count($_startDir, '/');
    if (@is_dir($_SERVER['DOCUMENT_ROOT'] . $_startDir)){
        $dirs2 = array_diff(scandir($_SERVER['DOCUMENT_ROOT'] . $_startDir), array('.', '..'));
    } else {
        return array();
    }
    $dirs = array();
    foreach ($dirs2 as $k => $dir){
        if (@is_dir($_SERVER['DOCUMENT_ROOT'] . $_startDir . '/' . $dir)) {
            $dirs[$_SERVER['DOCUMENT_ROOT'] . $_startDir . '/' . $dir] = utf8_encode($_startDir . '/' . $dir);
        }
    }
    if(!$_getSubdirs) {
        return $dirs;
    }
  
    $return = array();
    while(!empty($dirs)){
        $dir = array_shift($dirs);

        $depth = substr_count($dir, '/') - $startDepth;
        if(@is_dir($_SERVER['DOCUMENT_ROOT'].$dir) AND $depth <= $si->getConfig('dirdepth')){
            $return[$_SERVER['DOCUMENT_ROOT'].$dir] = utf8_encode($dir);
            $subdirs = array();
            foreach(array_diff(scandir($_SERVER['DOCUMENT_ROOT'].$dir), array( '.', '..' )) as $subdir) {
              if (@is_dir($_SERVER['DOCUMENT_ROOT'] . $dir . '/' . $subdir)) {
                  $subdirs[] = $dir . '/' . $subdir;
              }
            }
            array_splice($dirs, 0, 0, $subdirs);
        }
    }

    return $return;
}


function search_it_getFiles($_startDir = '', $_fileexts = array(), $_getSubdirs = false){
    $si = rex_addon::get('search_it');
  
    $return = array();
    $fileextPattern='';

    if(!empty($_fileexts)) {
      $fileextPattern = '~\.(' . implode('|', $_fileexts) . ')$~is';
    } else {
      $fileextPattern = '~\.([^.]+)$~is';
    }

    $startDepth = substr_count($_startDir, '/');
    if(@is_dir($_SERVER['DOCUMENT_ROOT'].$_startDir)) {
      $dirs2 = array_diff(scandir($_SERVER['DOCUMENT_ROOT'] . $_startDir), array('.', '..'));
    } else {
      return array();
    }
    $dirs = array();
    foreach($dirs2 as $k => $dir){
        if(@is_dir($_SERVER['DOCUMENT_ROOT'].$_startDir.'/'.$dir)) {
            $dirs[$_SERVER['DOCUMENT_ROOT'] . $_startDir . '/' . $dir] = $_startDir . '/' . $dir;
        } elseif(preg_match($fileextPattern, $dir)) {
            $return[] = utf8_encode($_startDir . '/' . $dir);
        }
    }
  
    if(!$_getSubdirs) {
        return $return;
    }
  
    while(!empty($dirs)){
        $dir = array_shift($dirs);

        $depth = substr_count($dir, '/') - $startDepth;
        if(@is_dir($_SERVER['DOCUMENT_ROOT'].$dir) AND $depth <= $si->getConfig('dirdepth')){
            $subdirs = array();
            foreach(array_diff(scandir($_SERVER['DOCUMENT_ROOT'].$dir), array( '.', '..' )) as $subdir) {
              if (@is_dir($_SERVER['DOCUMENT_ROOT'] . $dir . '/' . $subdir)) {
                  $subdirs[] = $dir . '/' . $subdir;
              } elseif (preg_match($fileextPattern, $subdir)) {
                  $return[] =  $dir . '/' . $subdir;
              }
            }
            array_splice($dirs, 0, 0, $subdirs);
        } elseif(preg_match($fileextPattern, $subdir)) {
            $return[] = $dir;
        }
    }
  
  return $return;
}


function search_it_register_extensions($_Aep){
    foreach($_Aep as $ep){
        rex_extension::register($ep, 'search_it_handle_extensionpoint');
    }
}


function search_it_handle_extensionpoint($_ep){
    $si = rex_addon::get('search_it');

    $_params = $_ep->getParams();
    $search_it = new search_it();

    switch($_ep->getName()){
        // delete article from index
        case 'ART_DELETED':
            $search_it->excludeArticle($_params['id']);
        break;

        // update meta-infos for article
        case 'ART_META_UPDATED':
        case 'ART_ADDED':
            foreach($search_it->includeColumns as $table => $columnArray){
                if($table == rex::getTable('article')){
                    foreach($columnArray as $column) {
                        $search_it->indexColumn($table, $column, 'id', $_params['id']);
                    }
                }
            }
        break;

        // exclude (if offline) or index (if online) article
        case 'ART_STATUS':
            if( $_params['status'] || $si->getConfig('indexoffline') ) {
                $search_it->indexArticle($_params['id'], $_params['clang']);
            } else {
                $search_it->excludeArticle($_params['id'], $_params['clang']);
            }

            foreach($search_it->includeColumns as $table => $columnArray){
                if($table == rex::getTable('article')){
                    foreach($columnArray as $column) {
                        $search_it->indexColumn($table, $column, 'id', $_params['id']);
                    }
                }
            }
        break;


        case 'ART_UPDATED':
            foreach($search_it->includeColumns as $table => $columnArray){
                if($table == rex::getTable('article')){
                    foreach($columnArray as $column) {
                        $search_it->indexColumn($table, $column, 'id', $_params['id']);
                    }
                }
            }
        break;

        case 'CAT_DELETED':
            echo rex_warning(rex_i18n::msg('search_it_cat_deleted'));
        break;

        case 'CAT_STATUS':
            if( $_params['status'] || $si->getConfig('indexoffline') ){
                foreach(search_it_getArticles(array($_params['id'])) as $art_id => $art_name) {
                    $search_it->indexArticle($art_id, $_params['clang']);
                }
            } else {
                foreach(search_it_getArticles(array($_params['id'])) as $art_id => $art_name) {
                    $search_it->excludeArticle($art_id, $_params['clang']);
                }
            }

            foreach($search_it->includeColumns as $table => $columnArray){
                if($table == rex::getTable('article')){
                    foreach($columnArray as $column) {
                        $search_it->indexColumn($table, $column, 'id', $_params['id']);
                    }
                }
            }
        break;

        case 'CAT_ADDED':
        case 'CAT_UPDATED':
            foreach($search_it->includeColumns as $table => $columnArray){
                if($table == rex::getTable('article')){
                    foreach($columnArray as $column) {
                        $search_it->indexColumn($table, $column, 'id', $_params['id']);
                    }
                }
            }
        break;

        case 'MEDIA_ADDED':
            foreach($search_it->includeColumns as $table => $columnArray){
                if($table == rex::getTable('media')){
                    foreach($columnArray as $column) {
                        $search_it->indexColumn($table, $column);
                    }
                }
            }
        break;

        case 'MEDIA_UPDATED':
            foreach($search_it->includeColumns as $table => $columnArray){
                if($table == rex::getTable('media')){
                    foreach($columnArray as $column) {
                        $search_it->indexColumn($table, $column, 'id', $_params['file_id']);
                    }
                }
            }
        break;

        case 'SLICE_UPDATED':
            $search_it->indexArticle($_params['article_id'],$_params['clang']);
        break;

        case 'SLICE_SHOW':
            // Das ist doch Müll? Nach der Message suchen?
            if( strpos($_params['subject'],'<div class="alert alert-success">') !== false AND (!empty($_params['function']) OR rex_request('slice_id','int',0) == $_params['slice_id'])) {
                $search_it->indexArticle($_params['article_id'], $_params['clang']);
            }
        break;
    }

    // Cache leeren
    $search_it->deleteCache();
}

function search_it_getSettingsFormSection($id = '', $title = '&nbsp;', $elements = array(), $ownsection = 'info', $collapse = false ){

    $return = '<fieldset id="'.$id.'">';
    $formElements = [];
    $fragment = new rex_fragment();

    foreach($elements as $element){
        $n = array();

        switch($element['type']){
            // HIDDEN
            case 'hidden':
                $n['label'] = '';
                $n['field'] = '<input type="hidden" name="'.$element['name'].'" value="'.$element['value'].'" />';
            break;

            // STRING
            case 'string':
                $n['label'] = '<label for="'.$element['id'].'">'.$element['label'].'</label>';
                $n['field'] = '<input type="text" name="'.$element['name'].'" class="form-control" id="'.$element['id'].'" value="'.$element['value'].'" />';
            break;

            // TEXT
            case 'text':
                $n['label'] = '<label for="'.$element['id'].'">'.$element['label'].'</label>';
                $n['field'] = '<textarea name="'.$element['name'].'" class="form-control" id="'.$element['id'].'" rows="10" cols="20">'.$element['value'].'</textarea>';
            break;

            // SELECT
            case 'select':
                $options = '';
                foreach($element['options'] as $option){
                  $options .= '<option value="'.$option['value'].'"'.($option['selected'] ? ' selected="selected"' : '').'>'.$option['name'].'</option>';
                }
                $n['label'] = '<label for="'.$element['id'].'">'.$element['label'].'</label>';
                $n['field'] = '<select class="form-control" id="'.$element['id'].'" size="1" name="'.$element['name'].'">'.$options.'</select>';
            break;

            // MULTIPLE SELECT
            case 'multipleselect':
                $options = '';
                foreach($element['options'] as $option){
                  $id = !empty($option['id'])?' id="'.$option['id'].'"':'';
                  $options .= '<option'.$id.' value="'.$option['value'].'"'.($option['selected'] ? ' selected="selected"' : '').'>'.$option['name'].'</option>';
                }
                $n['label']='<label for="'.$element['id'].'">'.$element['label'].'</label>';
                $n['field'] = '<select id="'.$element['id'].'" class="form-control" name="'.$element['name'].'" multiple="multiple" size="'.$element['size'].'"'.(!empty($element['disabled'])?' disabled="disabled"':'').'>'.$options.'</select>';
            break;

            // MULTIPLE CHECKBOXES
            case 'multiplecheckboxes':
                $checkboxes = '';
                foreach($element['options'] as $option){
                    $id = !empty($option['id'])?' id="'.$option['id'].'"':'';
                    $for = !empty($option['id'])?' for="'.$option['id'].'"':'';
                  $checkboxes .= '<div class="checkbox col-xs-3"><input type="checkbox" id="'.$option['id'].'" name="'.$element['name'].'" value="'.$option['value'].'" '.($option['checked'] ? ' checked="checked"' : '').' /> <label for="'.$option['id'].'">'.$option['name'].'</label></div>';
                }
                $n['label'] = '<label for="'.$element['id'].'">'.$element['label'].'</label>';
                $n['field'] = '<div class="rex-form-col-a rex-form-text"><div class="form-group">'.$checkboxes.'</div></div>';
            break;


            // RADIO
            case 'radio':

                $options = '';
                foreach($element['options'] as $option){
                    $n['label'] =' <label for="'.$option['id'].'">'.$option['label'].'</label>';
                    $n['field'] = '<input type="radio" name="'.$element['name'].'" value="'.$option['value'].'" class="rex-form-radio" id="'.$option['id'].'"'.($option['checked'] ? ' checked="checked"' : '').' />';
                    $formElements[] = $n;
                }
            break;

            // CHECKBOX
            case 'checkbox':
                $n['label'] = '<label for="'.$element['id'].'">'.$element['label'].'</label>';
                $n['field'] = '<input class="rex-form-checkbox" type="checkbox" name="'.$element['name'].'" id="'.$element['id'].'" value="'.$element['value'].'"'.($element['checked'] ? ' checked="checked"' : '').' />';
            break;

            // DIRECT OUTPUT
            case 'directoutput':
                if ($element['outputleft']!='') { $n['label'] = $element['outputleft']; }
                $n['field'] = $element['output'];
            break;
        }

        $formElements[] = $n;
    }
    $fragment->setVar('elements', $formElements, false);
    $return .= $fragment->parse('core/form/form.php').'</fieldset>';

    if ($ownsection) {
        $fragment = new rex_fragment();
        $fragment->setVar('class', $ownsection);
        $fragment->setVar('title', $title);
        $fragment->setVar('body', $return, false);
        if($collapse) {
            $fragment->setVar('collapse', true);
            $fragment->setVar('collapsed', true);
        }
        $return = $fragment->parse('core/page/section.php');
    }
    return $return;
}


function search_it_config_unserialize($_str){
    $conf = unserialize($_str);
  
    if(strpos($_str, '\\"') === false) {
        return $conf;
    }
  
    $return = array();
    if(is_array($conf)){
        foreach(unserialize($_str) as $k => $v){
            if(is_array($v)){
                $return[$k] = array();
                foreach($v as $k2 => $v2) {
                    if (is_array($v2)) {
                        $return[$k][$k2] = array();
                        foreach ($v2 as $k3 => $v3) {
                            if (is_array($v3)) {
                                $return[$k][$k2][$k3] = array();
                                foreach ($v3 as $k4 => $v4) {
                                    $return[$k][$k2][$k3][$k4] = stripslashes($v4);
                                }
                            } else {
                                $return[$k][$k2][$k3] = stripslashes($v3);
                            }
                        }
                    } else {
                        $return[$k][$k2] = stripslashes($v2);
                    }
                }
            } else {
                $return[$k] = stripslashes($v);
            }
        }
    }
  
    return $return;
}
