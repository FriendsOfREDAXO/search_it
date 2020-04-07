<?php

if (rex_post('config-submit', 'boolean')) {

    $posted_config = rex_post('search_config', [
        ['logicalmode', 'string'],
        ['textmode', 'string'],
        ['similarwordsmode', 'string'],
        ['similarwords_permanent', 'bool'],
        ['searchmode', 'string'],

        ['htaccess_user', 'string'],
        ['htaccess_pass', 'string'],

        ['indexoffline', 'bool'],
        ['automaticindex', 'bool'],
        ['reindex_cols_onforms', 'bool'],
        ['index_url_addon', 'bool'],

    ]);

    $changed = array_keys(array_merge(array_diff_assoc(array_map('serialize',$posted_config),array_map('serialize',$this->getConfig())), array_diff_assoc(array_map('serialize',$this->getConfig()),array_map('serialize',$posted_config))));
    foreach ( array(
                  'similarwordsmode',
                  'indexoffline',
              ) as $index ) {
        if ( in_array($index, $changed) ){
            echo rex_view::warning($this->i18n('search_it_settings_saved_warning')); break;
        } elseif ( is_array($this->getConfig($index)) && is_array($posted_config[$index]) ) { // Der Konfig-Wert ist ein Array
            if ( count(array_merge(
                array_diff_assoc(array_map('serialize',$this->getConfig($index)), array_map('serialize',$val)),
                array_diff_assoc(array_map('serialize',$val), array_map('serialize',$this->getConfig($index))) )) > 0 ) {
                echo rex_view::warning($this->i18n('search_it_settings_saved_warning')); break;
            }
        }
    }

    // do it
    $this->setConfig($posted_config);

    //tell it
    echo rex_view::success($this->i18n('search_it_settings_saved'));

}


$content = '';
$content3 = [];

// URL Addon Checkbox
$url_checkbox = [];
if(search_it_isUrlAddOnAvailable()) {
	$url_checkbox = [
            'type' => 'checkbox',
            'id' => 'search_it_index_url_addon',
            'name' => 'search_config[index_url_addon]',
            'label' => $this->i18n('search_it_settings_index_url_addon_label'),
            'value' => '1',
            'checked' => $this->getConfig('index_url_addon')
        ];
}

$content = search_it_getSettingsFormSection(
    'search_it_index',
    $this->i18n('search_it_settings_title_indexmode'),
    [
        [
            'type' => 'checkbox',
            'id' => 'search_it_indexoffline',
            'name' => 'search_config[indexoffline]',
            'label' => $this->i18n('search_it_settings_indexoffline'),
            'value' => '1',
            'checked' => $this->getConfig('indexoffline')
        ],
        [
            'type' => 'checkbox',
            'id' => 'search_it_automaticindex',
            'name' => 'search_config[automaticindex]',
            'label' => $this->i18n('search_it_settings_automaticindex_label'),
            'value' => '1',
            'checked' => $this->getConfig('automaticindex')
        ],
        [
            'type' => 'checkbox',
            'id' => 'search_it_reindex_cols_onforms',
            'name' => 'search_config[reindex_cols_onforms]',
            'label' => $this->i18n('search_it_settings_reindex_cols_onforms_label'),
            'value' => '1',
            'checked' => $this->getConfig('reindex_cols_onforms')
        ],
		$url_checkbox
    ],'edit'
);

$content .= search_it_getSettingsFormSection(
    'search_it_index',
    $this->i18n('search_it_settings_http_authbasic'),
    array(
        array(
            'type' => 'directoutput',
            'output' => '<strong>'.$this->i18n('search_it_settings_http_auth_desc').'</strong>',
            'where' => 'center'
        ),
        array(
            'type' => 'string',
            'id' => 'search_it_htaccess_user',
            'name' => 'search_config[htaccess_user]',
            'label' => $this->i18n('search_it_settings_htaccess_user'),
            'value' => !empty($this->getConfig('htaccess_user')) ? rex_escape($this->getConfig('htaccess_user')) : '',
        ),
        array(
            'type' => 'password',
            'id' => 'search_it_htaccess_pass',
            'name' => 'search_config[htaccess_pass]',
            'label' => $this->i18n('search_it_settings_htaccess_pass'),
            'value' => !empty($this->getConfig('htaccess_pass')) ? rex_escape($this->getConfig('htaccess_pass')) : '',
        ),
        /*array(
            'type' => 'checkbox',
            'id' => 'search_it_reindex_cols_onforms',
            'name' => 'search_config[reindex_cols_onforms]',
            'label' => $this->i18n('search_it_settings_reindex_cols_onforms_label'),
            'value' => '1',
            'checked' => $this->getConfig('reindex_cols_onforms')
        )*/
    ),'edit'
);
$content3[] = $content;

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
                    'value' => SEARCH_IT_SIMILARWORDS_NONE,
                    'selected' => $this->getConfig('similarwordsmode') == SEARCH_IT_SIMILARWORDS_NONE,
                    'name' => $this->i18n('search_it_settings_similarwords_none')
                ),
                array(
                    'value' => SEARCH_IT_SIMILARWORDS_SOUNDEX,
                    'selected' => $this->getConfig('similarwordsmode') == SEARCH_IT_SIMILARWORDS_SOUNDEX,
                    'name' => $this->i18n('search_it_settings_similarwords_soundex')
                ),
                array(
                    'value' => SEARCH_IT_SIMILARWORDS_METAPHONE,
                    'selected' => $this->getConfig('similarwordsmode') == SEARCH_IT_SIMILARWORDS_METAPHONE,
                    'name' => $this->i18n('search_it_settings_similarwords_metaphone')
                ),
                array(
                    'value' => SEARCH_IT_SIMILARWORDS_COLOGNEPHONE,
                    'selected' => $this->getConfig('similarwordsmode') == SEARCH_IT_SIMILARWORDS_COLOGNEPHONE,
                    'name' => $this->i18n('search_it_settings_similarwords_cologne')
                ),
                array(
                    'value' => SEARCH_IT_SIMILARWORDS_ALL,
                    'selected' => $this->getConfig('similarwordsmode') == SEARCH_IT_SIMILARWORDS_ALL,
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
            'label' => $this->i18n('search_it_settings_searchmode'),
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
    ),'edit'
);

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
