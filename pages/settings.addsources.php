<?php

if (rex_post('config-submit', 'boolean')) {

    $posted_config = rex_post('search_config', [
        ['include', 'array'],

        ['fileextensions','string'],
        ['indexmediapool', 'bool'],
        ['dirdepth', 'string'],
        ['indexfolders', 'array'],

    ]);

    // aus Komma-Listen arrays machen, bzw. arrays umformen
    if( !empty($posted_config['fileextensions']) ) {
        $fileExtensions = [];
        foreach(explode(',', $posted_config['fileextensions']) as $fileext) {
            $fileExtensions[] = trim($fileext);
        }
        $posted_config['fileextensions'] = $fileExtensions;
    } else {
        $posted_config['fileextensions'] = [];
    }

    if( !empty($posted_config['include']) && is_array($posted_config['include']) ) {
        $returnArray = [];
        foreach($posted_config['include'] as $include) {
            $includeArray = explode('`.`',$include);
            if(!array_key_exists($includeArray[0],$returnArray)) {
                $returnArray[$includeArray[0]] = [];
            }
            $returnArray[$includeArray[0]][] = $includeArray[1];
        }
        $posted_config['include'] = $returnArray;
    } else {
        $posted_config['include'] = [];
    }

    $changed = array_keys(array_merge(array_diff_assoc(array_map('serialize',$posted_config),array_map('serialize',$this->getConfig())), array_diff_assoc(array_map('serialize',$this->getConfig()),array_map('serialize',$posted_config))));
    foreach ( $posted_config as $index=>$val ) {
        if ( in_array($index, $changed) ){
            echo rex_view::warning($this->i18n('search_it_settings_saved_warning')); break;
        } elseif ( is_array($this->getConfig($index)) && is_array($val) ) { // Der Konfig-Wert ist ein Array
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
$formElements = [];


$content1 = '';
$sql_tables = rex_sql::factory();
foreach ( $sql_tables->getTablesAndViews() as $table ) {
    if ( false === strpos($table, 'search_it') ) {
        $options = [];
        $sql_columns = $sql_tables->showColumns($table);
        sort($sql_columns);
        foreach ( $sql_columns as $column ) {
            $options[] = array(
                'value' => rex_escape($table . '`.`' . $column['name']),
                'checked' => in_array($column['name'], (!empty($this->getConfig('include')[$table]) AND is_array($this->getConfig('include')[$table])) ? $this->getConfig('include')[$table] : []),
                'name' =>  $column['name'],
                'id' => $table . '.' . $column['name']
            );
        }

        $content1 .= '<div class="include_checkboxes">'.search_it_getSettingsFormSection(
            'search_it_include_'.$table,
            $table,
            array(
                array(
                    'type' => 'multiplecheckboxes',
                    'id' => 'search_it_include'.$table,
                    'name' => 'search_config[include][]',
                    'label' => '',
                    'size' => 20,
                    'options' => $options
                )
            ),'info',true
        ).'</div>';

    }
}


$fragment = new rex_fragment();
$fragment->setVar('class', 'edit');
$fragment->setVar('title', $this->i18n('search_it_settings_include'));
$fragment->setVar('body', $content1, false);
$content3[] =  $fragment->parse('core/page/section.php');


$options = array(
    array(
        'value' => '',
        'name' => '',
        'selected' => false,
        'id' => 'search_it_optiondummy'
    )
);
if (!empty($this->getConfig('indexfolders'))) {
    foreach ($this->getConfig('indexfolders') as $relative) {
        $options[] = array(
            'value' => $relative,
            'name' => $relative,
            'selected' => true
        );
    }
}
foreach (range(1, 30) as $depth) {
    $dirdepth_options[] = array(
        'value' => $depth,
        'name' => $depth,
        'selected' => $this->getConfig('dirdepth') == $depth
    );
}
$content3[] = search_it_getSettingsFormSection(
    'search_it_files',
    $this->i18n('search_it_settings_fileext_header'),
    array(
        array(
            'type' => 'string',
            'id' => 'search_it_settings_fileext_label',
            'name' => 'search_config[fileextensions]',
            'label' => rex_i18n::rawMsg('search_it_settings_fileext_label'),
            'value' => !empty($this->getConfig('fileextensions')) ? rex_escape(implode(',',$this->getConfig('fileextensions'))) : ''
        ),
        array(
            'type' => 'directoutput',
            'output' => '<div class="rex-form-row"></div>'
        ),
        array(
            'type' => 'checkbox',
            'id' => 'search_it_settings_file_mediapool',
            'name' => 'search_config[indexmediapool]',
            'label' => $this->i18n('search_it_settings_file_mediapool'),
            'value' => '1',
            'checked' => !empty($this->getConfig('indexmediapool'))
        ),
        array(
            'type' => 'directoutput',
            'output' => '<div class="rex-form-row"><br><label>'.$this->i18n('search_it_settings_additional_folders_label').'</label></div>'
        ),
        array(
            'type' => 'select',
            'id' => 'search_it_settings_file_dirdepth',
            'name' => 'search_config[dirdepth]',
            'label' => $this->i18n('search_it_settings_file_dirdepth_label'),
            'options' => $dirdepth_options
        ),
        array(
            'type' => 'multipleselect',
            'id' => 'search_it_settings_folders',
            'name' => 'search_config[indexfolders][]',
            'label' => $this->i18n('search_it_settings_folders_label'),
            'size' => 10,
            'options' => $options
        )
    ),'edit'
);

$fragment = new rex_fragment();
$fragment->setVar('content', $content3, false);
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
