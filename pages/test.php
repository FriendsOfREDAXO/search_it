<?php
$content = '';
$content2 = [];
$formElements = [];
$dumpstr = '';


$buttons = '<button class="btn btn-save" type="submit" name="search_it_test" value="1" ' . rex::getAccesskey($this->i18n('search_it_test_submit'), 'save') . '>' . $this->i18n('search_it_test_submit') . '</button></form>';

$content2[] = search_it_getSettingsFormSection(
    'search_it_test',
    $this->i18n('search_it_test_legend'),
    array(
        array(
            'type' => 'directoutput',
            'output' => '<form id="search_it_settings_form" action="' . rex_url::currentBackendPage() . '" method="post">'
        ),
        array(
            'type' => 'select',
            'id' => 'search_it_test',
            'name' => 'search_it_test[]',
            'label' => $this->i18n('search_it_test_mode'),
            'options' => [
                ['value' => '0', 'selected'=>'', 'name' => $this->i18n('search_it_test_mode_all')]//,
                //['value' => 'article', 'name' => $this->i18n('search_it_test_mode_article')],
                //['value' => 'db', 'name' => $this->i18n('search_it_test_mode_db')],
                //['value' => 'media', 'name' => $this->i18n('search_it_test_mode_media')]
                ]
        ),
        array(
            'type' => 'string',
            'id' => 'search_it_test_keywords',
            'name' => 'search_it_test_keywords',
            'label' => $this->i18n('search_it_test_keywords'),
            'value' => !empty(rex_post('search_it_test_keywords','string')) ? rex_escape(rex_post('search_it_test_keywords','string')) : ''
        ),
        array(
            'type' => 'directoutput',
            'output' => $buttons
        ),
    ),'edit'
);



$fragment = new rex_fragment();
$fragment->setVar('class', 'info');
$fragment->setVar('title', $this->i18n('search_it_test_result'));

if($request = rex_request('search_it_test_keywords', 'string')) {
    $search_it = new search_it();
    $result = $search_it->search($request);

    ob_start();
    echo '<p>'.$this->i18n('search_it_test_result_object').'</p>';
    dump($result);
    echo '<p>'.$this->i18n('search_it_test_hits').': '.count($result['hits']).'</p>';
    foreach($result['hits'] as $hit) {
        dump($hit);
    }
    $dumpstr = ob_get_clean();
}
$fragment->setVar('body', $dumpstr, false);
$content2[] =  $fragment->parse('core/page/section.php');



$fragment = new rex_fragment();
$fragment->setVar('content', $content2, false);
$content .= $fragment->parse('core/page/grid.php');


echo $content;

echo "<script>$('.sf-dump-toggle span').trigger('click');</script>";