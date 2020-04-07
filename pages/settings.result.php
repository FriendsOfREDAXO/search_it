<?php

if (rex_post('config-submit', 'boolean')) {

    $posted_config = rex_post('search_config', [
        ['surroundtags', 'array'],
        ['limit', 'array'],
        ['maxteaserchars', 'string'],
        ['maxhighlightchars', 'string'],
        ['highlight', 'string'],
        ['highlighterclass', 'string'],

    ]);

    // do it
    $this->setConfig($posted_config);

    //tell it
    echo rex_view::success($this->i18n('search_it_settings_saved'));

}


$content = '';
$content2 = [];
$formElements = [];


$sample = <<<EOT
Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.
Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi.
EOT;
$sampleoutput = '<div id="search_it_sample_wrapper">
        <h5 class="rex-form-text">'.$this->i18n('search_it_settings_highlight_sample').':<strong>"velit esse" accusam</strong></h5>
        <div id="search_it_sample">';
$search_it = new search_it();
$search_it->setSearchString('"velit esse" accusam');
$search_it->parseSearchString('"velit esse" accusam');
if ( $this->getConfig('highlight') == 'array' ) {
    $sampleoutput .= '<pre>';
    $sampleoutput .= print_r($search_it->getHighlightedText($sample), true);
    $sampleoutput .= '</pre>';
} else {
    $sampleoutput .= $search_it->getHighlightedText($sample);
}
$sampleoutput .= '</div></div>';


$content2a = search_it_getSettingsFormSection(
    'search_it_highlighting',
    $this->i18n('search_it_settings_highlight_header'),
    array(
        array(
            'type' => 'string',
            'id' => 'search_it_surroundtags_start',
            'name' => 'search_config[surroundtags][0]',
            'label' => $this->i18n('search_it_settings_surroundtags_start'),
            'value' => isset($this->getConfig('surroundtags')[0]) ? rex_escape($this->getConfig('surroundtags')[0]) : ''
        ),
        array(
            'type' => 'string',
            'id' => 'search_it_surroundtags_end',
            'name' => 'search_config[surroundtags][1]',
            'label' => $this->i18n('search_it_settings_surroundtags_end'),
            'value' => isset($this->getConfig('surroundtags')[1]) ? rex_escape($this->getConfig('surroundtags')[1]) : ''
        ),
        array(
            'type' => 'hidden',
            'name' => 'search_config[limit][0]',
            'value' => '0'
        ),
        array(
            'type' => 'string',
            'id' => 'search_it_limit',
            'name' => 'search_config[limit][1]',
            'label' => $this->i18n('search_it_settings_limit'),
            'value' => !empty($this->getConfig('limit')[1]) && is_numeric($this->getConfig('limit')[1]) ? $this->getConfig('limit')[1] : ''
        ),
        array(
            'type' => 'string',
            'id' => 'search_it_maxteaserchars',
            'name' => 'search_config[maxteaserchars]',
            'label' => $this->i18n('search_it_settings_maxteaserchars'),
            'value' => !empty($this->getConfig('maxteaserchars')) && is_numeric($this->getConfig('maxteaserchars')) ? $this->getConfig('maxteaserchars') : ''
        ),
        array(
            'type' => 'string',
            'id' => 'search_it_maxhighlightchars',
            'name' => 'search_config[maxhighlightchars]',
            'label' => $this->i18n('search_it_settings_maxhighlightchars'),
            'value' => !empty($this->getConfig('maxhighlightchars')) && is_numeric($this->getConfig('maxhighlightchars')) ? $this->getConfig('maxhighlightchars') : ''
        ),
        array(
            'type' => 'select',
            'id' => 'search_it_highlight',
            'name' => 'search_config[highlight]',
            'label' => $this->i18n('search_it_settings_highlight_label'),
            'options' => array(
                array(
                    'value' => 'sentence',
                    'selected' => $this->getConfig('highlight') == 'sentence',
                    'name' => $this->i18n('search_it_settings_highlight_sentence')
                ),
                array(
                    'value' => 'paragraph',
                    'selected' => $this->getConfig('highlight') == 'paragraph',
                    'name' => $this->i18n('search_it_settings_highlight_paragraph')
                ),
                array(
                    'value' => 'surroundtext',
                    'selected' => $this->getConfig('highlight') == 'surroundtext',
                    'name' => $this->i18n('search_it_settings_highlight_surroundtext')
                ),
                array(
                    'value' => 'surroundtextsingle',
                    'selected' => $this->getConfig('highlight') == 'surroundtextsingle',
                    'name' => $this->i18n('search_it_settings_highlight_surroundtextsingle')
                ),
                array(
                    'value' => 'teaser',
                    'selected' => $this->getConfig('highlight') == 'teaser',
                    'name' => $this->i18n('search_it_settings_highlight_teaser')
                ),
                array(
                    'value' => 'array',
                    'selected' => $this->getConfig('highlight') == 'array',
                    'name' => $this->i18n('search_it_settings_highlight_array')
                ),
            )
        ),
        array(
            'type' => 'directoutput',
            'output' => '<div class="rex-form-row">'.$sampleoutput.'</div>'
        ),
    ),'edit'
);

$content2[] = $content2a;

$content2[] = search_it_getSettingsFormSection(
    'search_it_highlighterclass',
    $this->i18n('search_it_settings_search_highlighter'),
    array(
        array(
            'type' => 'string',
            'id' => 'search_it_highlighterclass',
            'name' => 'search_config[highlighterclass]',
            'label' => $this->i18n('search_it_settings_highlighterclass'),
            'value' => !empty($this->getConfig('highlighterclass')) ? $this->getConfig('highlighterclass') : ''
        ),
    ),'edit'
);

$fragment = new rex_fragment();
$fragment->setVar('content', $content2, false);
$content .= $fragment->parse('core/page/grid.php');


$formElements = [];
$n = [];
$n['field'] = '<button class="btn btn-save rex-form-aligned" type="submit" name="config-submit" value="1" ' . rex::getAccesskey($this->i18n('search_it_settings_submitbutton'), 'save') . '>' . $this->i18n('search_it_settings_submitbutton') . '</button>';
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