<?php

    $curDir = __DIR__;
    require_once $curDir . '/functions/functions_plaintext.php';


    if ( rex::isBackend() ) {

        rex_extension::register('SEARCH_IT_PLAINTEXT', 'search_it_doPlaintext');

        rex_view::addJsFile($this->getAssetsUrl('jquery.ui.custom.js'));

        /*if(!file_exists($settingFile = $curDir.'/settings.conf')){
            search_it_plaintext_saveSettings(array(
                'order' => 'selectors,regex,textile,striptags',
                'selectors' => "head,\nscript",
                'regex' => '',
                'textile' => true,
                'striptags' => true,
                'processparent' => false
            ));
        }
        if (!$this->hasConfig()) {
            $this->setConfig(array(
                'order' => 'selectors,regex,textile,striptags',
                'selectors' => "head,\nscript",
                'regex' => '',
                'textile' => true,
                'striptags' => true,
                'processparent' => false
            ));
        }


        $this->setConfig(search_it_config_unserialize(rex_file::get($settingFile)));*/
    }
