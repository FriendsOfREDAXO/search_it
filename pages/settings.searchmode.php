<?php

echo rex_view::title($this->i18n('title') . ' <small>(' . $this->getProperty('version') . ')</small>');

if (rex_post('config-submit', 'boolean')) {

    $posted_config = rex_post('search_config', [
        ['logicalmode', 'string'],
        ['textmode', 'string'],
        ['similarwordsmode', 'string'],
        ['similarwords_permanent', 'bool'],
        ['searchmode', 'string'],
    ]);

    $changed = array_keys(array_merge(array_diff_assoc(array_map('serialize', $posted_config), array_map('serialize', $this->getConfig())), array_diff_assoc(array_map('serialize', $this->getConfig()), array_map('serialize', $posted_config))));
    $warnings = [];
    if (in_array('similarwordsmode', $changed)) {
        $warnings[] = $this->i18n('search_it_settings_saved_warning_similarwords');
    }
    if (!empty($warnings)) {
        echo rex_view::warning(implode('<br>', $warnings));
    }

    // do it
    $this->setConfig($posted_config);

    //tell it
    echo rex_view::success($this->i18n('search_it_settings_saved'));

}

$content3 = [];

$content3[] = search_it_getSettingsFormSection(
    'search_it_modi',
    $this->i18n('search_it_settings_modi_header'),
    array(
        array(
            'type' => 'select',
            'id' => 'search_it_logicalmode',
            'name' => 'search_config[logicalmode]',
            'label' => $this->i18n('search_it_settings_logicalmode'),
            'options' => array(
                array(
                    'value' => 'and',
                    'selected' => $this->getConfig('logicalmode') == 'and',
                    'name' => $this->i18n('search_it_settings_logicalmode_and')
                ),
                array(
                    'value' => 'or',
                    'selected' => $this->getConfig('logicalmode') == 'or',
                    'name' => $this->i18n('search_it_settings_logicalmode_or')
                )
            )
        ),
        array(
            'type' => 'select',
            'id' => 'search_it_textmode',
            'name' => 'search_config[textmode]',
            'label' => $this->i18n('search_it_settings_textmode'),
            'options' => array(
                array(
                    'value' => 'plain',
                    'selected' => $this->getConfig('textmode') == 'plain',
                    'name' => $this->i18n('search_it_settings_textmode_plain')
                ),
                array(
                    'value' => 'html',
                    'selected' => $this->getConfig('textmode') == 'html',
                    'name' => $this->i18n('search_it_settings_textmode_html')
                ),
                array(
                    'value' => 'both',
                    'selected' => $this->getConfig('textmode') == 'both',
                    'name' => $this->i18n('search_it_settings_textmode_both')
                )
            )
        ),
        array(
            'type' => 'select',
            'id' => 'search_it_similarwords_mode',
            'name' => 'search_config[similarwordsmode]',
            'label' => $this->i18n('search_it_settings_similarwords_label'),
            'options' => array(
                array(
                    'value' => \FriendsOfRedaxo\SearchIt\SearchIt::SIMILARWORDS_NONE,
                    'selected' => $this->getConfig('similarwordsmode') == \FriendsOfRedaxo\SearchIt\SearchIt::SIMILARWORDS_NONE,
                    'name' => $this->i18n('search_it_settings_similarwords_none')
                ),
                array(
                    'value' => \FriendsOfRedaxo\SearchIt\SearchIt::SIMILARWORDS_SOUNDEX,
                    'selected' => $this->getConfig('similarwordsmode') == \FriendsOfRedaxo\SearchIt\SearchIt::SIMILARWORDS_SOUNDEX,
                    'name' => $this->i18n('search_it_settings_similarwords_soundex')
                ),
                array(
                    'value' => \FriendsOfRedaxo\SearchIt\SearchIt::SIMILARWORDS_METAPHONE,
                    'selected' => $this->getConfig('similarwordsmode') == \FriendsOfRedaxo\SearchIt\SearchIt::SIMILARWORDS_METAPHONE,
                    'name' => $this->i18n('search_it_settings_similarwords_metaphone')
                ),
                array(
                    'value' => \FriendsOfRedaxo\SearchIt\SearchIt::SIMILARWORDS_COLOGNEPHONE,
                    'selected' => $this->getConfig('similarwordsmode') == \FriendsOfRedaxo\SearchIt\SearchIt::SIMILARWORDS_COLOGNEPHONE,
                    'name' => $this->i18n('search_it_settings_similarwords_cologne')
                ),
                array(
                    'value' => \FriendsOfRedaxo\SearchIt\SearchIt::SIMILARWORDS_ALL,
                    'selected' => $this->getConfig('similarwordsmode') == \FriendsOfRedaxo\SearchIt\SearchIt::SIMILARWORDS_ALL,
                    'name' => $this->i18n('search_it_settings_similarwords_all')
                )
            )
        ),
        array(
            'type' => 'checkbox',
            'id' => 'search_it_similarwords_permanent',
            'name' => 'search_config[similarwords_permanent]',
            'label' => $this->i18n('search_it_settings_similarwords_permanent'),
            'value' => '1',
            'checked' => !empty($this->getConfig('similarwords_permanent'))
        ),
        array(
            'type' => 'select',
            'id' => 'search_it_searchmode',
            'name' => 'search_config[searchmode]',
            'label' => $this->i18n('search_it_settings_searchmode_label'),
            'options' => array(
                array(
                    'value' => 'like',
                    'selected' => $this->getConfig('searchmode') == 'like',
                    'name' => $this->i18n('search_it_settings_searchmode_like')
                ),
                array(
                    'value' => 'match',
                    'selected' => $this->getConfig('searchmode') == 'match',
                    'name' => $this->i18n('search_it_settings_searchmode_match')
                )
            )
        )
    ), 'edit'
);

// Leere zweite Spalte damit es nicht über die ganze Breite geht
$content3[] = '';

$fragment = new rex_fragment();
$fragment->setVar('content', $content3, false);
$content = $fragment->parse('core/page/grid.php');


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
