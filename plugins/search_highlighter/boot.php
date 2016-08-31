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


    }
