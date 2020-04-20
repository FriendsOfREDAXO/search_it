<?php

$addon = rex_addon::get('search_it');
$form = rex_config_form::factory($addon->name);

$form->addFieldset($this->i18n('search_it_settings'));

$sql_tables = rex_sql::factory();
foreach ($sql_tables->getTablesAndViews() as $table) {
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
