<?php
$ajax = rex_request('ajax', 'string');


if(!empty($ajax)) {
    ob_end_clean();
    require 'ajax.php';
    exit;
}

echo rex_view::title($this->i18n('title').' <small>('.$this->getProperty('version').')</small>');

rex_be_controller::includeCurrentPageSubPath();