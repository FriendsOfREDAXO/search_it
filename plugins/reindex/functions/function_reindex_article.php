<?php
function search_it_reindex_article($_ep){

  $_params = $_ep->getParams();
  $get = array();

  $get['article_id'] = rex_get('article_id','int',0) == 0 ? rex_request('article_id', 'int', rex_article::getCurrentId()) : rex_get('article_id','int',0);

  $get['clang'] = rex_get('clang','int',0) == 0 ? rex_request('clang', 'int', rex_clang::getCurrentId()) : rex_get('clang','int',0);

  if( rex_get('ctype','string','') == '' AND rex_request('ctype','string','') != '' ){
      $get['ctype'] = rex_request('ctype');
  }
  if( rex_get('mode','string','') == '' AND rex_request('mode','string','') != '' ){
      $get['mode'] = rex_request('mode');
  }
  
  $get['func'] = 'reindex';
  
  $_params['subject'][] = '<a href="index.php?'.http_build_query($get, null, '&amp;').'" class="rex-active">'.rex_addon::get('search_it')->i18n('search_it_reindex_article').'</a>';

  return $_params['subject'];
}
