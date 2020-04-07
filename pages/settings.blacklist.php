<?php

if (rex_post('config-submit', 'boolean')) {

    $posted_config = rex_post('search_config', [

        ['blacklist', 'string'],
        ['exclude_article_ids', 'array'],
        ['exclude_category_ids', 'array'],

    ]);

    // aus Komma-Listen arrays machen, bzw. arrays umformen
    if( !empty($posted_config['blacklist']) ) {
        $posted_config['blacklist'] = explode(',',$posted_config['blacklist']);
    } else {
        $posted_config['blacklist'] = [];
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
$content2 = [];
$formElements = [];



$categories = [];
foreach(search_it_getCategories(false) as $id => $name){
  $categories[] = array(
      'value' => $id,
      'selected' => !empty($this->getConfig('exclude_category_ids')) AND is_array($this->getConfig('exclude_category_ids')) AND in_array($id,$this->getConfig('exclude_category_ids')),
      'name' => $name.' ('.$id.')'
  );
}
$articles = [];
foreach(search_it_getArticles() as $id => $name){
  $articles[] = array(
      'value' => $id,
      'selected' => !empty($this->getConfig('exclude_article_ids')) AND is_array($this->getConfig('exclude_article_ids')) AND in_array($id,$this->getConfig('exclude_article_ids')),
      'name' => $name.' ('.$id.')'
  );
}
$content2[] = search_it_getSettingsFormSection(
    'search_it_exclude',
    $this->i18n('search_it_settings_exclude'),
    array(
        array(
            'type' => 'text',
            'id' => 'search_it_settings_exclude_blacklist',
            'name' => 'search_config[blacklist]',
            'label' => $this->i18n('search_it_settings_exclude_blacklist'),
            'value' => !empty($this->getConfig('blacklist')) ? rex_escape(implode(',',$this->getConfig('blacklist'))) : ''
        ),
        array(
            'type' => 'multipleselect',
            'id' => 'search_it_exclude_article_ids',
            'name' => 'search_config[exclude_article_ids][]',
            'label' => $this->i18n('search_it_settings_exclude_articles'),
            'size' => 15,
            'options' => $articles
        ),
        array(
            'type' => 'multipleselect',
            'id' => 'search_it_exclude_category_ids',
            'name' => 'search_config[exclude_category_ids][]',
            'label' => $this->i18n('search_it_settings_exclude_categories'),
            'size' => 15,
            'options' => $categories
        )
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
