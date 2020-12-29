<?php

if (rex::isBackend() && rex::getUser() && 'search_it' == rex_be_controller::getCurrentPagePart(1)) {
    rex_view::addCssFile($this->getAssetsUrl('docs.css'));
}
