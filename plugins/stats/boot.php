<?php

    $curDir = __DIR__;
    require_once $curDir . '/functions/functions_stats.php';


    rex_extension::register('SEARCH_IT_SEARCH_EXECUTED', 'search_it_stats_storekeywords');

    if ( rex::isBackend() ) {

        rex_extension::register('SEARCH_IT_PAGE_MAINTENANCE', 'search_it_stats_addtruncate');

        rex_view::addCssFile($this->getAssetsUrl('stats.css'));

        /*if(!file_exists($settingFile = $curDir.'/settings.conf')){
            search_it_stats_saveSettings(array(
                'maxtopSearchitems' => 10,
                'searchtermselect' => '',
                'searchtermselectmonthcount' => 12
            ));
        }

        $this->setConfig(search_it_config_unserialize(rex_file::get($settingFile)));
        */
    }
