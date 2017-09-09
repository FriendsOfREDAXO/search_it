<?php
$content = '';
$content2 = '';
$formElements = [];

$content2[] = search_it_getSettingsFormSection(
    'search_it_test',
    $this->i18n('search_it_settings_test_legend'),
    array(
        array(
            'type' => 'select',
            'id' => 'search_it-test',
            'name' => 'search_it-test[]',
            'label' => $this->i18n('search_it-test_mode'),
            'options' => [
                ['value' => '0', 'selected'=>'', 'name' => $this->i18n('search_it-test_mode-all')]//,
                //['value' => 'article', 'name' => $this->i18n('search_it-test_mode-article')],
                //['value' => 'db', 'name' => $this->i18n('search_it-test_mode-db')],
                //['value' => 'media', 'name' => $this->i18n('search_it-test_mode-media')]
                ]
        ),
        array(
            'type' => 'string',
            'id' => 'search_it-test-keywords',
            'name' => 'search_it-test-keywords',
            'label' => $this->i18n('search_it-testkeywords'),
            'value' => rex_request('search_it-test-keywords', 'string', '')
        ),
    ),'edit'
);



$fragment = new rex_fragment();
$fragment->setVar('content', $content2, false);
$content .= $fragment->parse('core/page/grid.php');




$formElements = [];
$n = [];
$n['field'] = '<button class="btn btn-save rex-form-aligned" type="submit" name="search_it-test" value="1" ' . rex::getAccesskey($this->i18n('search_it_settings_test-submit'), 'save') . '>' . $this->i18n('search_it_settings_test-submit') . '</button>';
$formElements[] = $n;
$fragment = new rex_fragment();
$fragment->setVar('flush', true);
$fragment->setVar('elements', $formElements, false);
$buttons = $fragment->parse('core/form/submit.php');


$fragment = new rex_fragment();
$fragment->setVar('buttons', $buttons, false);
$content .= $fragment->parse('core/page/section.php');

echo '
<form id="search_it_settings_form" action="' . rex_url::currentBackendPage() . '" method="post">
' . $content . '
</form>';

if ($request = rex_request('search_it-test-keywords', 'string')) {

    //tell it
    echo rex_view::info($this->i18n('search_it_settings_test_notice'));
    if($request) { 
        $search_it = new search_it(); 
        $result = $search_it->search($request); 
        dump($result);
        echo rex_view::info($this->i18n('search_it_settings_test_hits').' '.count($result['hits']));        
        foreach($result['hits'] as $hit)
        dump($hit);
    }

}

    echo "<script>$('.sf-dump-toggle span').trigger('click');</script>";