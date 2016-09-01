<?php

$file = rex_file::get(rex_path::plugin('search_it','documentation').'/pages/general.md');
$Parsedown = new Parsedown();
$content =  ''.$Parsedown->text($file);

$fragment = new rex_fragment();
$fragment->setVar('title', $this->i18n('search_it_title'),'');
$fragment->setVar('class', 'info', false);
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');
