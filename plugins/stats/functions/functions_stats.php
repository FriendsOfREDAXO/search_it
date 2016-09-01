<?php

function search_it_stats_storekeywords($_ep){
    $_params = $_ep->getSubject();

    $stats = new search_it_stats();
    $stats->insert($_params['searchterm'], $_params['count']);
}

function search_it_stats_addtruncate($_ep){
    $subject = $_ep->getSubject();
    $st = rex_plugin::get('search_it','stats');

    if (rex_request('func') == 'truncate') {
        $stats = new search_it_stats();
        $stats->truncate();

        $st->setConfig(array(
            'maxtopsearchitems' => 10,
            'searchtermselect' => '',
            'searchtermselectmonthcount' => 12
        ));

        $subject = rex_view::success($st->i18n('search_it_stats_truncate_done')).$subject;
    }

    $subject .= '<p class="rex-tx1">' . $st->i18n('search_it_stats_truncate') . '</p>
    <p><a class="btn btn-primary" onclick="return confirm(\'' . $st->i18n('search_it_stats_truncate_confirm') . '\');" href="index.php?page=search_it/generate&amp;func=truncate" ><span>' . $st->i18n('search_it_stats_truncate_button') . '</span></a></p>';

    return $subject;
}

function search_it_getStatSection($_id, $_title, $_content){
    return '<section id="' . $_id . '" class="rex-form-col-1"><legend>' . $_title . '</legend>
<div class="rex-form-wrapper">
  <div class="rex-form-row">
  <div class="rex-area-content"><p>
    ' . $_content . '</p>
  </div>
  </div>
</div>
</fieldset>';
}

function search_it_stats_bytesize($_value){
    $units = array(
        'Byte',
        'KByte',
        'MByte',
        'GByte',
        'TByte',
        'PByte'
    );

    $i = 0;
    if ($_value > 0) {
        while ($_value > 1024) {
            $_value /= 1024;
            $i++;
        }

        $dec = ($i > 1) ? 2 : 0;
        return number_format($_value, $dec, ',', '') . ' ' . $units[$i];
    }
}
