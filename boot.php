<?php
    if ( !defined('SEARCH_IT_ART_EXCLUDED') ) {
        define('SEARCH_IT_ART_EXCLUDED',0);
        define('SEARCH_IT_ART_IDNOTFOUND',1);
        define('SEARCH_IT_ART_GENERATED',2);
        define('SEARCH_IT_ART_REDIRECT',3);

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
                'SLICE_UPDATED'
            );
            search_it_register_extensions($extensionPoints);
        }

        //set default Values on installation
        if (!$this->hasConfig()) {
            $this->setConfig('limit',array(0,10));
        }
        rex_view::addJsFile( $this->getAssetsUrl('search_it.js') );
        rex_view::addCssFile( $this->getAssetsUrl('search_it.css') );
	}


