<?php
    if ( !defined('SEARCH_IT_ART_EXCLUDED') ) {
        define('SEARCH_IT_ART_EXCLUDED',0);
        define('SEARCH_IT_ART_IDNOTFOUND',1);
        define('SEARCH_IT_ART_GENERATED',2);
        define('SEARCH_IT_ART_REDIRECT',3);
        define('SEARCH_IT_ART_ERROR',4);
        define('SEARCH_IT_ART_NOTOK',5);
        define('SEARCH_IT_ART_404',6);

        define('SEARCH_IT_URL_EXCLUDED',0);
        define('SEARCH_IT_URL_GENERATED',2);
        define('SEARCH_IT_URL_REDIRECT',3);
        define('SEARCH_IT_URL_ERROR',4);
        define('SEARCH_IT_URL_NOTOK',5);
        define('SEARCH_IT_URL_404',6);

        define('SEARCH_IT_FILE_NOEXIST',0);
        define('SEARCH_IT_FILE_XPDFERR_OPENSRC',1);
        define('SEARCH_IT_FILE_XPDFERR_OPENDEST',2);
        define('SEARCH_IT_FILE_XPDFERR_PERM',3);
        define('SEARCH_IT_FILE_XPDFERR_OTHER',4);
        define('SEARCH_IT_FILE_FORBIDDEN_EXTENSION',5);
        define('SEARCH_IT_FILE_GENERATED',6);
        define('SEARCH_IT_FILE_EMPTY',7);

        define('SEARCH_IT_SIMILARWORDS_NONE',0);
        define('SEARCH_IT_SIMILARWORDS_SOUNDEX',1);
        define('SEARCH_IT_SIMILARWORDS_METAPHONE',2);
        define('SEARCH_IT_SIMILARWORDS_COLOGNEPHONE',4);
        define('SEARCH_IT_SIMILARWORDS_ALL',7);
    }

    $curDir = __DIR__;
    require_once $curDir . '/functions/functions_search_it.php';

    if ( rex_request('search_highlighter', 'string', '') != '' && rex_addon::get('search_it')->getConfig('highlighterclass') != '' ) {
        rex_extension::register('OUTPUT_FILTER', 'search_it_search_highlighter_output');
    }

    if ( rex_addon::get('search_it')->getConfig('reindex_cols_onforms') == true ) {
        rex_extension::register('REX_FORM_SAVED', 'search_it_reindex_cols');
        rex_extension::register('REX_YFORM_SAVED', 'search_it_reindex_cols');
        rex_extension::register('YFORM_DATA_DELETED','search_it_reindex_cols');
        rex_extension::register('REX_FORM_DELETED', 'search_it_reindex_cols');
    }
    if ( rex_addon::get('cronjob')->isAvailable() && !rex::isSafeMode() ) {
        rex_cronjob_manager::registerType('rex_cronjob_reindex');
    }

    if ( rex_request('search_it_build_index','string','') != '' ) {
        rex_extension::register('ART_CONTENT', function (rex_extension_point $_ep) {
            $params = $_ep->getParams();
            $article_id = $params['article']->getArticleId();
            if ( rex_request('search_it_build_index','string','') == 'redirect' ) { $article_id = ''; }
            $subject = '<!-- search_it ' . $article_id . ' -->' . $_ep->getSubject() . '<!-- /search_it ' . $article_id . ' -->';
            return $subject;

        });
    }
    if ( rex::isBackend() && rex::getUser() ) {
        // automatic indexing
        if ( rex_addon::get('search_it')->getConfig('automaticindex') == true ){
            $extensionPoints = array(
                'ART_DELETED',
                'ART_META_UPDATED',
                'ART_STATUS',
                'ART_ADDED',
                'ART_UPDATED',
                'CAT_DELETED',
                'CAT_STATUS',
                'CAT_ADDED',
                'CAT_UPDATED',
                'MEDIA_ADDED',
                'MEDIA_UPDATED',
                'MEDIA_DELETED',
                'SLICE_ADDED',
                'SLICE_DELETED',
                'SLICE_UPDATED',
            );

            rex_extension::register($extensionPoints, 'search_it_handle_extensionpoint');

        }

        //set default Values on installation
        if (!$this->hasConfig()) {
            $this->setConfig('limit',array(0,10));
        }
        if ( strpos(rex_request('page','string',''),'search_it') !== false ) {
            rex_view::addJsFile($this->getAssetsUrl('search_it.js'));
            rex_view::addCssFile($this->getAssetsUrl('search_it.css'));
        }

	}


