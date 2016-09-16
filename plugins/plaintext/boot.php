<?php

    $curDir = __DIR__;
    require_once $curDir . '/functions/functions_plaintext.php';

    if ( rex::isBackend() ) {

        rex_extension::register('SEARCH_IT_PLAINTEXT', 'search_it_doPlaintext');

        //set default Values on installation
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

    }
