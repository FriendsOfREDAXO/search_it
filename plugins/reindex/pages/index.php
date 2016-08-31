<?php

$content = '';

//echo rex_view::title($this->i18n('title'));

//include rex_be_controller::getCurrentPageObject()->getSubPath();

//echo rex_be_controller::getCurrentPageObject()->getSubPath();
//echo rex_getUrl(1);
/*echo '<br>'.rex_url::media('test.jpg')."\n";
echo '<br>'.rex_url::frontend('test.jpg');*/
$content .= '<br>'.rex_url::currentBackendPage();

$fragment = new rex_fragment();
$fragment->setVar('title', $this->i18n('search_it_reindex_plugin_info'),'');
$fragment->setVar('class', 'info', false);
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');