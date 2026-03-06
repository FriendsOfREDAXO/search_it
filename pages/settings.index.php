<?php

echo rex_view::title($this->i18n('title') . ' <small>(' . $this->getProperty('version') . ')</small>');

if (rex_post('config-submit', 'boolean')) {

    $posted_config = rex_post('search_config', [
        ['htaccess_user', 'string'],
        ['htaccess_pass', 'string'],

        ['indexoffline', 'bool'],
        ['automaticindex', 'bool'],
        ['reindex_cols_onforms', 'bool'],
        ['index_url_addon', 'bool'],

        ['index_without_ssl_verification', 'bool'],
        ['index_host', 'string'],

    ]);

    $changed = array_keys(array_merge(array_diff_assoc(array_map('serialize', $posted_config), array_map('serialize', $this->getConfig())), array_diff_assoc(array_map('serialize', $this->getConfig()), array_map('serialize', $posted_config))));
    $warnings = [];
    foreach (['indexoffline'] as $index) {
        if (in_array($index, $changed)) {
            $warnings[] = $this->i18n('search_it_settings_saved_warning');
            break;
        }
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

// URL Addon Checkbox
$url_checkbox = [];
if (search_it_isUrlAddOnAvailable()) {
    $url_checkbox = [
        'type' => 'checkbox',
        'id' => 'search_it_index_url_addon',
        'name' => 'search_config[index_url_addon]',
        'label' => $this->i18n('search_it_settings_index_url_addon_label'),
        'value' => '1',
        'checked' => $this->getConfig('index_url_addon')
    ];
}
// SSL verify
$ssl_verify = [];
if (rex_version::compare(rex::getVersion(), '5.13', '>=')) {
    $ssl_verify = [
        'type' => 'checkbox',
        'id' => 'search_it_index_without_ssl_verification',
        'name' => 'search_config[index_without_ssl_verification]',
        'label' => $this->i18n('search_it_settings_index_without_ssl_verification_label'),
        'value' => '1',
        'checked' => $this->getConfig('index_without_ssl_verification')
    ];
}

// Linke Spalte: Checkboxen
$content3[] = search_it_getSettingsFormSection(
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
        ,
        $ssl_verify
        ,
    ], 'edit'
);

// Rechte Spalte: Index-URL + HTTP Auth (nur relevant wenn Socket-Modus aktiv)
if (true == rex_config::get('search_it', 'dont_use_socket')) {
    $fragment = new rex_fragment();
    $fragment->setVar('class', 'default');
    $fragment->setVar('title', $this->i18n('search_it_settings_index_special_title'));
    $fragment->setVar('body', '<p>' . $this->i18n('search_it_settings_dont_use_socket_active') . '</p>', false);
    $content3[] = $fragment->parse('core/page/section.php');
} else {
    $content3[] = search_it_getSettingsFormSection(
        'search_it_index_special',
        $this->i18n('search_it_settings_index_special_title'),
        [
            [
                'type' => 'directoutput',
                'output' => '<h3>' . $this->i18n('search_it_settings_http_authbasic') . '</h3><p>' . $this->i18n('search_it_settings_http_auth_desc') . '</p>',
            ],
            [
                'type' => 'string',
                'id' => 'search_it_htaccess_user',
                'name' => 'search_config[htaccess_user]',
                'label' => $this->i18n('search_it_settings_htaccess_user'),
                'value' => !empty($this->getConfig('htaccess_user')) ? rex_escape($this->getConfig('htaccess_user')) : '',
            ],
            [
                'type' => 'password',
                'id' => 'search_it_htaccess_pass',
                'name' => 'search_config[htaccess_pass]',
                'label' => $this->i18n('search_it_settings_htaccess_pass'),
                'value' => !empty($this->getConfig('htaccess_pass')) ? rex_escape($this->getConfig('htaccess_pass')) : '',
            ],
            [
                'type' => 'directoutput',
                'output' => '<h3>' . $this->i18n('search_it_settings_index_url_title') . '</h3><p>' . $this->i18n('search_it_settings_index_host_desc') . '</p>',
            ],
            [
                'type' => 'string',
                'id' => 'search_it_index_host',
                'name' => 'search_config[index_host]',
                'label' => $this->i18n('search_it_settings_index_host_label'),
                'value' => !empty($this->getConfig('index_host')) ? rex_escape($this->getConfig('index_host')) : '',
            ],
        ], 'edit'
    );
}

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
