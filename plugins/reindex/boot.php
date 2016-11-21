<?php

    $curDir = __DIR__;
    require_once $curDir . '/functions/function_reindex.php';
    require_once $curDir . '/functions/function_reindex_article.php';

    if ( rex::isBackend() ) {

        if (rex_get('func') == 'reindex' AND rex_get('article_id', 'int') AND 0 <= rex_get('clang', 'int', -1)) {
            rex_extension::register('PACKAGES_INCLUDED ', function () {

                $search_it = new search_it();
                $search_it->indexArticle(rex_article::getCurrentId(), rex_clang::getCurrentId());

                rex_extension::register('PAGE_CONTENT_OUTPUT', function ($_params) {
                    echo rex_view::success($this->i18n('search_it_reindex_done'));
                });
            });
        }

    }

    if ( rex_plugin::get('search_it','reindex')->isInstalled() ) {
        rex_extension::register('REX_FORM_SAVED', 'search_it_reindex');
        rex_extension::register('REX_YFORM_SAVED', 'search_it_reindex');
        rex_extension::register('REX_FORM_DELETED', 'search_it_reindex');

        //Den extension point gibt es nicht mehr, erzeugte einen Link im Backend
        //rex_extension::register('PAGE_CONTENT_MENU', 'search_it_reindex_article');
    }

