<?php
function search_it_reindex($_ep){

    $_params = $_ep->getParams();
    $si = rex_addon::get('search_it');

    $id = 0;

    if(!empty($_params['yform'])){
        $tablename = $_params['form']->params['main_table'];
        //$wherecondition = $_params['sql']->wherevar;
        $wherecondition = $_params['form']->params['main_where'];
    } else {
        $tablename = $_params['form']->tableName;
        $wherecondition = $_params['form']->whereCondition;
    }

    $last_id = intval($_params['sql']->getLastId());

    if(!array_key_exists($tablename,$si->getConfig('include')) OR !is_array($si->getConfig('include')[$tablename])) {
        return true;
    }

    if(empty($id)) { $id = $last_id; }

    $search_it = new search_it;
    foreach($si->getConfig('include')[$tablename] as $col) {
        $search_it->indexColumn($tablename, $col, false, false, false, false, $wherecondition);
    }

    return true;
}
