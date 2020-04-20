<?php

$hidden_tables = ["rex_action","rex_clang","rex_config","rex_media_manager_type","rex_media_manager_type_effect","rex_metainfo_field","rex_metainfo_type","rex_user","rex_user_role","rex_ydeploy_migration","rex_yform_email_template","rex_yform_field","rex_yform_history","rex_yform_history_field","rex_yform_table","rex_yrewrite_alias","rex_yrewrite_domain","rex_yrewrite_forward","rex_article_slice_history","rex_cronjob","rex_url_generate","rex_yform_history","rex_yform_history_field","rex_yrewrite_alias","rex_yrewrite_domain","rex_yrewrite_forward"];

rex_config::set("search_it", "hidden_tables", $hidden_tables);

$addon = rex_addon::get('search_it');
$form = rex_config_form::factory($addon->name);

$form->addFieldset($this->i18n('search_it_settings'));



$sql_tables = rex_sql::factory();
foreach ($sql_tables->getTablesAndViews() as $table) {
    $hidden_tables = rex_config::get("search_it", "hidden_tables");

    if (in_array($table, $hidden_tables)) {
        continue;
    };

    $field = $form->addSelectField('similarwordsmode', $value = null, ['class'=>'form-control selectpicker']);
    $field->setAttribute('multiple', 'multiple');
    $field->setLabel($table);
    $select = $field->getSelect();
    $sql_columns = $sql_tables->showColumns($table);
    sort($sql_columns);
    foreach ($sql_columns as $column) {
        $select->addOption($column['name'], $table . '`.`' . $column['name']);
    }
}

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', $addon->i18n('search_it_settings'), false);
$fragment->setVar('body', $form->get(), false);
echo $fragment->parse('core/page/section.php');

$form_name = $form->getName();
if (rex_post($form_name.'_save')) {
    rex_view::info($this->i18n('search_it_settings_saved'));
}
