<?php

if (rex_post('config-submit', 'boolean')) {

    $posted_config = rex_post('search_config', [
        ['logicalmode', 'string'],
        ['textmode', 'string'],
        ['similarwordsmode', 'string'],
        ['similarwords_permanent', 'bool'],
        ['searchmode', 'string'],

        ['indexmode', 'string'],
        ['indexoffline', 'bool'],
        ['automaticindex', 'bool'],
        ['ep_outputfilter', 'bool'],

    ]);


    // do it
    $this->setConfig($posted_config);

    //tell it
    echo rex_view::success($this->i18n('search_it_settings_saved'));


    /*    echo '<pre>';
    var_dump(rex_post('search_config'));
    echo "\n";
    var_dump( $this->getConfig());
    echo '</pre>';*/

    foreach( array_keys(array_merge(array_diff_assoc($posted_config,$this->getConfig(), array_diff_assoc($this->getConfig(),$posted_config)))) as $changed) {
        if(in_array($changed, array(
            'indexmode',
            'indexoffline',
            'automaticindex',
            'blacklist',
            'exclude_article_ids',
            'exclude_category_ids',
            'include',
            'fileextensions',
            'indexmediapool',
            'dirdepth',
            'indexfolders',
            'ep_outputfilter'
        ))) {
                echo rex_view::warning($this->i18n('search_it_settings_saved_warning')); break;
            }
    }
}


$content = '';
$formElements = [];


$content[] = search_it_getSettingsFormSection(
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



$content[] = search_it_getSettingsFormSection(
    'search_it_index',
    $this->i18n('search_it_settings_title_indexmode'),
    array(
        array(
            'type' => 'select',
            'id' => 'search_it_settings_indexmode',
            'name' => 'search_config[indexmode]',
            'label' => $this->i18n('search_it_settings_indexmode_label'),
            'options' => array(
                array(
                    'value' => '0',
                    'name' => $this->i18n('search_it_settings_indexmode_viahttp'),
                    'selected' => $this->getConfig('indexmode') == '0',
                ),
                array(
                    'value' => '1',
                    'name' => $this->i18n('search_it_settings_indexmode_viacache'),
                    'selected' => $this->getConfig('indexmode') == '1',
                ),
                array(
                    'value' => '2',
                    'name' => $this->i18n('search_it_settings_indexmode_viacachetpl'),
                    'selected' => $this->getConfig('indexmode') == '2',
                )
            )
        ),
        array(
            'type' => 'checkbox',
            'id' => 'search_it_indexoffline',
            'name' => 'search_config[indexoffline]',
            'label' => $this->i18n('search_it_settings_indexoffline'),
            'value' => '1',
            'checked' => !empty($this->getConfig('indexoffline'))
        ),
        array(
            'type' => 'checkbox',
            'id' => 'search_it_automaticindex',
            'name' => 'search_config[automaticindex]',
            'label' => $this->i18n('search_it_settings_automaticindex_label'),
            'value' => '1',
            'checked' => !empty($this->getConfig('automaticindex'))
        ),
        array(
            'type' => 'checkbox',
            'id' => 'search_it_ep_outputfilter',
            'name' => 'search_config[ep_outputfilter]',
            'label' => $this->i18n('search_it_settings_ep_outputfilter_label'),
            'value' => '1',
            'checked' => !empty($this->getConfig('ep_outputfilter'))
        )
    ),'edit'
);


$fragment = new rex_fragment();
$fragment->setVar('content', $content, false);
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