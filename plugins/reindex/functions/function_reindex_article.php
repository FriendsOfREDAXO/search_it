<?php
function search_it_reindex_article($_ep){

  $_params = $_ep->getParams();
  $get = $_GET;

  if(!array_key_exists('article_id', $get)){
      $get['article_id'] = rex_request('article_id', 'int', rex_article::getCurrentId());
  }
  if(!array_key_exists('clang', $get)){
      $get['clang'] = rex_request('clang', 'int', rex_clang::getCurrentId());
  }
  if(!array_key_exists('ctype', $get) AND array_key_exists('ctype', $_REQUEST)){
      $get['ctype'] = rex_request('ctype');
  }
  if(!array_key_exists('mode', $get) AND array_key_exists('mode', $_REQUEST)){
      $get['mode'] = rex_request('mode');
  }
  
  $get['func'] = 'reindex';
  
  $_params['subject'][] = '<a href="index.php?'.http_build_query($get, null, '&amp;').'" class="rex-active">'.rex_addon::get('search_it')->i18n('search_it_reindex_article').'</a>';

  return $_params['subject'];
}
