<?php
$ajax = rex_request('ajax', 'string');


if (!empty($ajax)) {
    ob_end_clean();
    require 'ajax.php';
    exit;
}

rex_be_controller::includeCurrentPageSubPath();
