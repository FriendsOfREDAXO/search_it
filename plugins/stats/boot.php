<?php

    $curDir = __DIR__;
    require_once $curDir . '/functions/functions_stats.php';

    if (rex_request('search_it_test', 'string', '') == '') {
        rex_extension::register('SEARCH_IT_SEARCH_EXECUTED', 'search_it_stats_storekeywords');
    }
    if ( rex::isBackend() ) {

        rex_extension::register('SEARCH_IT_PAGE_MAINTENANCE', 'search_it_stats_addtruncate');

        rex_view::addCssFile($this->getAssetsUrl('stats.css'));

        if (!$this->hasConfig()) {
            $this->setConfig(array(
                'maxtopsearchitems' => 10,
                'searchtermselect' => '',
                'searchtermselectmonthcount' => 12
            ));
        }
    }
