<?php

    $curDir = __DIR__;
    //require_once $curDir . '/functions/functions_autocomplete.php';

    if ( rex::isBackend() ) {

        rex_view::addCssFile($this->getAssetsUrl('jquery.suggest.css'));
        rex_view::addJsFile($this->getAssetsUrl('jquery.suggest.js'));

        if (!$this->hasConfig()) {
            $this->setConfig(array(
                'modus' => 'keywords',
                'maxSuggestion' => '10',
                'similarwordsmode' => '0',
                'autoSubmitForm' => 1
            ));
        }
    }
