<?php

function search_it_getArticles($cats = false) {
    $si = rex_addon::get('search_it');

    $whereCats = [];
    if(is_array($cats)){
        foreach($cats as $catID) {
            $whereCats[] = "path LIKE '%|" . $catID . "|%'";
        }
    }

    $return = [];
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

    $return = [];

    if(!empty($_cats)){
        $whereCats = [];
        $sqlCats = [];
        if(is_array($_cats)){
            foreach($_cats as $catID){
                $whereCats[] = "path LIKE '%|".intval($catID)."|%'";
                $sqlCats[] = intval($catID);
            }
        }

        $return = [];
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
        return [];
    }
    $dirs = [];
    foreach ($dirs2 as $k => $dir){
        if (@is_dir($_SERVER['DOCUMENT_ROOT'] . $_startDir . '/' . $dir)) {
            $dirs[$_SERVER['DOCUMENT_ROOT'] . $_startDir . '/' . $dir] = utf8_encode($_startDir . '/' . $dir);
        }
    }
    if(!$_getSubdirs) {
        return $dirs;
    }

    $return = [];
    while(!empty($dirs)){
        $dir = array_shift($dirs);

        $depth = substr_count($dir, '/') - $startDepth;
        if(@is_dir($_SERVER['DOCUMENT_ROOT'].$dir) AND $depth <= $si->getConfig('dirdepth')){
            $return[$_SERVER['DOCUMENT_ROOT'].$dir] = utf8_encode($dir);
            $subdirs = [];
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

function search_it_getFiles($_startDir = '', $_fileexts = [], $_getSubdirs = false){
    $si = rex_addon::get('search_it');

    $return = [];
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
        return [];
    }
    $dirs = [];
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
            $subdirs = [];
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



function search_it_handle_extensionpoint($_ep){
    $si = rex_addon::get('search_it');

    $_params = $_ep->getParams();
    $_subject = $_ep->getSubject();
    $includeColumns = is_array(rex_addon::get('search_it')->getConfig('include')) ? rex_addon::get('search_it')->getConfig('include') : [];
    $search_it = new search_it();

    switch($_ep->getName()){
        // delete article from index
        case 'ART_DELETED':
            $search_it->unindexArticle($_params['id']);
            break;

        // update meta-infos for article
        case 'ART_META_UPDATED':
        case 'ART_ADDED':
        case 'ART_UPDATED':
            foreach( $includeColumns as $table => $columnArray){
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
                $search_it->unindexArticle($_params['id'], $_params['clang']);
            }

            foreach( $includeColumns as $table => $columnArray){
                if($table == rex::getTable('article')){
                    foreach($columnArray as $column) {
                        $search_it->indexColumn($table, $column, 'id', $_params['id']);
                    }
                }
            }
            break;


        case 'CAT_DELETED':
            //echo rex_view::warning(rex_i18n::rawMsg('search_it_cat_deleted'));
            break;

        case 'CAT_STATUS':
            if( $_params['status'] || $si->getConfig('indexoffline') ){
                foreach( search_it_getArticles(array($_params['id'])) as $art_id => $art_name ) {
                    $search_it->indexArticle($art_id, $_params['clang']);
                }
            } else {
                $search_it->unindexArticle($_params['id'], $_params['clang']); // der Kategorie-Artikel ist schon offline gesetzt, und wird von _getArticles nicht mehr geholt
                foreach( search_it_getArticles(array($_params['id'])) as $art_id => $art_name ) {
                    $search_it->unindexArticle($art_id, $_params['clang']);
                }
            }

            foreach( $includeColumns as $table => $columnArray ){
                if($table == rex::getTable('article')){
                    foreach($columnArray as $column) {
                        $search_it->indexColumn($table, $column, 'id', $_params['id']);
                    }
                }
            }
            break;

        case 'CAT_ADDED':
        case 'CAT_UPDATED':
            foreach ( $includeColumns as $table => $columnArray ){
                if($table == rex::getTable('article')){
                    foreach($columnArray as $column) {
                        $search_it->indexColumn($table, $column, 'id', $_params['id']);
                    }
                }
            }
            break;

        case 'MEDIA_ADDED':
        case 'MEDIA_DELETED':
            foreach( $includeColumns as $table => $columnArray){
                if($table == rex::getTable('media')){
                    foreach($columnArray as $column) {
                        // extension point liefert nicht die id des neuen/entfernten Mediums
                        $search_it->indexColumn($table, $column);
                    }
                }
            }
            break;

        case 'MEDIA_UPDATED':
            foreach( $includeColumns as $table => $columnArray){
                if($table == rex::getTable('media')){
                    foreach($columnArray as $column) {
                        $search_it->indexColumn($table, $column, 'id', $_params['id']);
                    }
                }
            }
            break;

        case 'SLICE_UPDATED':
        case 'SLICE_DELETED':
        case 'SLICE_ADDED':
            $search_it->indexArticle($_params['article_id'],$_params['clang']);
            break;


    }

    // Cache leeren
    $search_it->deleteCache();
}

function search_it_getSettingsFormSection($id = '', $title = '&nbsp;', $elements = [], $ownsection = 'info', $collapse = false ) {

    $return = '<fieldset id="'.$id.'">';
    $formElements = [];
    $fragment = new rex_fragment();

    foreach($elements as $element) {
		if(count($element) == 0) {
			// Skip empty elements
			continue;
		}

        $n = [];

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

            // STRING
            case 'password':
                $n['label'] = '<label for="'.$element['id'].'">'.$element['label'].'</label>';
                $n['field'] = '<input type="password" name="'.$element['name'].'" class="form-control" id="'.$element['id'].'" value="'.$element['value'].'" />';
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
                    //$checkboxes .= '<div class="checkbox col-xs-3"><input type="checkbox" id="'.$option['id'].'" name="'.$element['name'].'" value="'.$option['value'].'" '.($option['checked'] ? ' checked="checked"' : '').' /> <label for="'.$option['id'].'">'.$option['name'].'</label></div>';
                    $formUnterElements = [];
                    $un = [];
                    $un['label'] = '<label for="'.$option['id'].'">'.$option['name'].'</label>';
                    $un['field'] = '<input type="checkbox" id="'.$option['id'].'" name="'.$element['name'].'" value="'.$option['value'].'" '.($option['checked'] ? ' checked="checked"' : '').' />';
                    $un['highlight'] = $option['checked'];
                    $formUnterElements[] = $un;
                    $fragment = new rex_fragment();
                    $fragment->setVar('grouped', true);
                    $fragment->setVar('elements', $formUnterElements, false);
                    $checkboxes .= '<div class="col-xs-12">'.$fragment->parse('core/form/checkbox.php').'</div>';
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
                $formUnterElements = [];
                $un = [];
                $un['label'] = '<label for="'.$element['id'].'">'.$element['label'].'</label>';
                $un['field'] = '<input type="checkbox" id="'.$element['id'].'" name="'.$element['name'].'" value="'.$element['value'].'" '.($element['checked'] ? ' checked="checked"' : '').' />';
                $un['highlight'] = $element['checked'];
                $formUnterElements[] = $un;
                $fragmentun = new rex_fragment();
                $fragmentun->setVar('elements', $formUnterElements, false);
                $n['field'] = $fragmentun->parse('core/form/checkbox.php');

                break;

            // DIRECT OUTPUT
            case 'directoutput':
                if ( isset($element['where']) && $element['where']!='') {
                    if ( $element['where'] == 'left') {
                        $n['label'] = $element['output'];
                    } else {
                        $n['header'] = $element['output'];
                    }
                } else {
                    $n['field'] = $element['output'];
                }

                break;
        }

        $formElements[] = $n;
    }
    $fragment->setVar('elements', $formElements, false);
    $return .= $fragment->parse('core/form/form.php').'</fieldset>';

    if ($ownsection) {
        $fragment = new rex_fragment();
        $fragment->setVar('class', $ownsection);
        $fragment->setVar('title', $title,false);
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

    $return = [];
    if(is_array($conf)){
        foreach(unserialize($_str) as $k => $v){
            if(is_array($v)){
                $return[$k] = [];
                foreach($v as $k2 => $v2) {
                    if (is_array($v2)) {
                        $return[$k][$k2] = [];
                        foreach ($v2 as $k3 => $v3) {
                            if (is_array($v3)) {
                                $return[$k][$k2][$k3] = [];
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


/**
 * Phonetik für die deutsche Sprache nach dem Kölner Verfahren
 *
 * Die Kölner Phonetik (auch Kölner Verfahren) ist ein phonetischer Algorithmus,
 * der Wörtern nach ihrem Sprachklang eine Zeichenfolge zuordnet, den phonetischen
 * Code. Ziel dieses Verfahrens ist es, gleich klingenden Wörtern den selben Code
 * zuzuordnen, um bei Suchfunktionen eine Ähnlichkeitssuche zu implementieren. Damit
 * ist es beispielsweise möglich, in einer Namensliste Einträge wie "Meier" auch unter
 * anderen Schreibweisen, wie "Maier", "Mayer" oder "Mayr", zu finden.
 *
 * Die Kölner Phonetik ist, im Vergleich zum bekannteren Russell-Soundex-Verfahren,
 * besser auf die deutsche Sprache abgestimmt. Sie wurde 1969 von Postel veröffentlicht.
 *
 * Infos: http://www.uni-koeln.de/phil-fak/phonetik/Lehre/MA-Arbeiten/magister_wilz.pdf
 *
 * Die Umwandlung eines Wortes erfolgt in drei Schritten:
 *
 * 1. buchstabenweise Codierung von links nach rechts entsprechend der Umwandlungstabelle
 * 2. entfernen aller mehrfachen Codes
 * 3. entfernen aller Codes "0" ausser am Anfang
 *
 * Beispiel  Der Name "Müller-Lüdenscheidt" wird folgendermaßen kodiert:
 *
 * 1. buchstabenweise Codierung: 60550750206880022
 * 2. entfernen aller mehrfachen Codes: 6050750206802
 * 3. entfernen aller Codes "0": 65752682
 *
 * Umwandlungstabelle:
 * ============================================
 * Buchstabe      Kontext                  Code
 * -------------  -----------------------  ----
 * A,E,I,J,O,U,Y                            0
 * H                                        -
 * B                                        1
 * P              nicht vor H               1
 * D,T            nicht vor C,S,Z           2
 * F,V,W                                    3
 * P              vor H                     3
 * G,K,Q                                    4
 * C              im Wortanfang
 *                vor A,H,K,L,O,Q,R,U,X     4
 * C              vor A,H,K,O,Q,U,X
 *                ausser nach S,Z           4
 * X              nicht nach C,K,Q         48
 * L                                        5
 * M,N                                      6
 * R                                        7
 * S,Z                                      8
 * C              nach S,Z                  8
 * C              im Wortanfang ausser vor
 *                A,H,K,L,O,Q,R,U,X         8
 * C              nicht vor A,H,K,O,Q,U,X   8
 * D,T            vor C,S,Z                 8
 * X              nach C,K,Q                8
 * --------------------------------------------
 *
 * ---------------------------------------------------------------------
 * Support/Info/Download: https://github.com/deezaster/germanphonetic
 * ---------------------------------------------------------------------
 *
 * @package    x3m
 * @version    1.3
 * @author     Andy Theiler <andy@x3m.ch>
 * @copyright  Copyright (c) 1996 - 2014, Xtreme Software GmbH, Switzerland (www.x3m.ch)
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
function soundex_ger($word)
{
    //echo "<br>input: <b>" . $word . "</b>";

    $code    = "";
    $word    = strtolower($word);

    if (strlen($word) < 1) { return ""; }

    // Umwandlung: v->f, w->f, j->i, y->i, ph->f, ä->a, ö->o, ü->u, ß->ss, é->e, è->e, ê->e, à->a, á->a, â->a, ë->e
    $word = str_replace(array("ç","v","w","j","y","ph","ä","ö","ü","ß","é","è","ê","à","á","â","ë"), array("c","f","f","i","i","f","a","o","u","ss","e","e","e","a","a","a","e"), $word);
    //echo "<br>optimiert1: <b>" . $word . "</b>";

    // Nur Buchstaben (keine Zahlen, keine Sonderzeichen)
    $word = preg_replace('/[^a-zA-Z]/', '', $word);
    //echo "<br>optimiert2: <b>" . $word . "</b>";



    $wordlen = strlen($word);
    $char    = str_split($word);


    // Sonderfälle bei Wortanfang (Anlaut)
    if ($char[0] == 'c')
    {
        if ($wordlen == 1)
        {
            $code = 8;
            $x = 1;
        }
        else
        {
            // vor a,h,k,l,o,q,r,u,x
            switch ($char[1]) {
                case 'a':
                case 'h':
                case 'k':
                case 'l':
                case 'o':
                case 'q':
                case 'r':
                case 'u':
                case 'x':
                    $code = "4";
                    break;
                default:
                    $code = "8";
                    break;
            }
            $x = 1;
        }
    }
    else
    {
        $x = 0;
    }
    for (; $x < $wordlen; $x++)
    {
        switch ($char[$x]) {
            case 'a':
            case 'e':
            case 'i':
            case 'o':
            case 'u':
                $code .= "0";
                break;
            case 'b':
            case 'p':
                $code .= "1";
                break;
            case 'd':
            case 't':
                if ($x+1 < $wordlen) {
                    switch ($char[$x+1]) {
                        case 'c':
                        case 's':
                        case 'z':
                            $code .= "8";
                            break;
                        default:
                            $code .= "2";
                            break;
                    }
                }
                else {
                    $code .= "2";
                }
                break;
            case 'f':
                $code .= "3";
                break;
            case 'g':
            case 'k':
            case 'q':
                $code .= "4";
                break;
            case 'c':
                if ($x+1 < $wordlen) {
                    switch ($char[$x+1]) {
                        case 'a':
                        case 'h':
                        case 'k':
                        case 'o':
                        case 'q':
                        case 'u':
                        case 'x':
                            switch ($char[$x-1]) {
                                case 's':
                                case 'z':
                                    $code .= "8";
                                    break;
                                default:
                                    $code .= "4";
                            }
                            break;
                        default:
                            $code .= "8";
                            break;
                    }
                }
                else {
                    $code .= "8";
                }
                break;
            case 'x':
                if ($x > 0) {
                    switch ($char[$x-1]) {
                        case 'c':
                        case 'k':
                        case 'q':
                            $code .= "8";
                            break;
                        default:
                            $code .= "48";
                            break;
                    }
                }
                else {
                    $code .= "48";
                }
                break;
            case 'l':
                $code .= "5";
                break;
            case 'm':
            case 'n':
                $code .= "6";
                break;
            case 'r':
                $code .= "7";
                break;
            case 's':
            case 'z':
                $code .= "8";
                break;
        }

    }
    //echo "<br>code1: <b>" . $code . "</b><br />";

    // Mehrfach Codes entfernen
    $code =  preg_replace("/(.)\\1+/", "\\1", $code);
    //echo "<br>code2: <b>" . $code . "</b><br />";
    // entfernen aller Codes "0" ausser am Anfang
    $codelen      = strlen($code);
    $num          = [];
    $num          = str_split($code);
    $phoneticcode = $num[0];

    for ($x = 1; $x < $codelen; $x++)
    {
        if ($num[$x] != "0") {
            $phoneticcode .= $num[$x];
        }
    }

    return $phoneticcode;
}


// ex search highlighter plugin
function search_it_search_highlighter_output($_ep){

    $subject = $_ep->getSubject();

    $suchbegriffe = rex_request('search_highlighter', 'string', '');

    $si = rex_addon::get('search_it');
    $beginn = '<span class=\'' . $si->getConfig('highlighterclass').'\'>';
    $ende = '</span>';
    $tags = array($beginn, $ende);

    preg_match('/<body[^>]*>(.*?)<\/body>/is', $subject, $matches);

    $body = search_it_search_highlighter_getHighlightedText($matches[0], $suchbegriffe, $tags);
    $subject = preg_replace('/<body[^>]*>(.*?)<\/body>/is',$body,$subject);

    return $subject;

}

function search_it_search_highlighter_getHighlightedText($_subject, $_searchString, $_tags){
    preg_match_all('~(?:(\+*)"([^"]*)")|(?:(\+*)(\S+))~is', $_searchString, $matches, PREG_SET_ORDER);

    $searchterms = [];
    foreach ($matches as $match) {
        if (count($match) == 5) {
            // words without double quotes (foo)
            $word = $match[4];
        } elseif (!empty($match[2])) {
            // words with double quotes ("foo bar")
            $word = $match[2];
        } else {
            continue;
        }
        $searchterms[] = preg_quote($word, '~');
    }

    $hidemask = '7341fqtb99';
    $all = preg_replace_callback('~<[^<]*?(?:=\'[^\']*?|="[^"]*?)(' . implode('|', $searchterms) . ')[^"\']*?(?:"|\')~',
        function ($match){
            $hidemask = '7341fqtb99';
            return str_replace($match[1],$hidemask.$match[1],$match[0]);
        } ,
        $_subject);

    $all = preg_replace('~(?<!<|'.$hidemask.')(' . implode('|', $searchterms) . ')(?![^<]*\>)~ims', $_tags[0] . '$1' . $_tags[1], $all);
    $all = str_replace($hidemask,'',$all);
    return $all;
}

/**
 * Prüft ob das URL Addon in der kompatiblen Version verfügbar ist.
 * @return bool
 */
function search_it_isUrlAddOnAvailable() {
	return ( rex_addon::get('url')->isAvailable() && rex_string::versionCompare(\rex_addon::get('url')->getVersion(), '1.5', '>='));
}

/**
 * Ermittelt den Namen der Tabelle des URL Addons
 * @return string
 */
function search_it_getUrlAddOnTableName() {

    if (search_it_isUrlAddOnAvailable()) {

        $tableName = null;

        $sql = rex_sql::factory();
        $allTables = $sql->getTables();

        foreach( $allTables as $oneTable ) {
            if ( strpos($oneTable, \Url\UrlManagerSql::TABLE_NAME) !== false ) {
                $tableName = $oneTable;
                break;
            }
        }

        return $tableName;
    }
}

// ex reindex plugin
function search_it_reindex_cols($_ep){
    if ($_ep->getSubject() instanceof Exception) {
        return $_ep->getSubject();
    }

    $_params = $_ep->getParams();

    $includeColumns = is_array(rex_addon::get('search_it')->getConfig('include')) ? rex_addon::get('search_it')->getConfig('include') : [];
    $search_it = new search_it;

    $didcol = false;
    $did = false;
    $tablename = '';
    $wherecondition = false;

    if(!empty($_params['yform'])){
        $tablename = $_params['form']->params['main_table'];
        $wherecondition = $_params['form']->params['main_where'];
    } else if ( !empty($_params['form'])) {
        $tablename = $_params['form']->getTableName();
        $wherecondition = $_params['form']->getWhereCondition();
    } else if ( !empty($_params['table']) ) {
        $tablename = $_params['table']->getTableName();
        $didcol = 'id';
        $did = $_params['data_id'];
    } else {
        rex_logger::factory()->info('keine Angabe welche Tabelle indexiert werden soll');
        return false;
    }

    if(!array_key_exists($tablename, $includeColumns) OR !is_array($includeColumns[$tablename])) {
        return true;
    }

    foreach($includeColumns[$tablename] as $col) {
        $search_it->indexColumn($tablename, $col, $didcol, $did, false, false, $wherecondition);
    }

    return true;
}
