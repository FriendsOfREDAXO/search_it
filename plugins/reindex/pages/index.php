<?php

$content = '<br>'.rex_url::currentBackendPage();

$fragment = new rex_fragment();
$fragment->setVar('title', $this->i18n('search_it_reindex_plugin_info'),'');
$fragment->setVar('class', 'info', false);
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');