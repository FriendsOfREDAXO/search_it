<?php

    $curDir = __DIR__;
    require_once $curDir . '/functions/functions_search_highlighter.php';

    if (rex_request('search_highlighter', 'string', '') != "") {
        rex_extension::register('OUTPUT_FILTER', 'search_it_search_highlighter_output');
    }

    $stil = rex_request('stil','string','');
    if (!empty($stil)){
        search_it_search_highlighter_stil_css($stil);
    }
    if ( rex::isBackend() ) {

        /*if(!file_exists($settingFile = $curDir.'/settings.conf')){
            search_it_search_highlighter_saveSettings(array(
                'tag' => 'span',
                'class' => '',
                'inlineCSS' => '' ,
                'stilEinbinden' => 1,
                'stil' => 'stil1',
                'stil1' => 'font-weight: bold; background-color: #E8E63B; color: #000000;',
                'stil2' => 'font-style: italic; font-size: 1.1em;',
                'stilEigen' => ''
            ));
        }
        $this->setConfig(search_it_config_unserialize(rex_file::get($settingFile)));*/
    }
