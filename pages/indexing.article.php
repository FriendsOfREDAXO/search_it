<?php

$addon = rex_addon::get('search_it');
$form = rex_config_form::factory($addon->name);

$field = $form->addCheckboxField('index_articles');
$field->setLabel("");
$field->addOption($this->i18n('search_it_settings_index_articles'), '1');

$field = $form->addCheckboxField('indexoffline');
$field->setLabel("");
$field->addOption($this->i18n('search_it_settings_indexoffline'), '1');

$field = $form->addCheckboxField('automaticindex');
$field->setLabel("");
$field->addOption($this->i18n('search_it_settings_automaticindex_label'), '1');

$field = $form->addLinklistField('exclude_article_ids');
// legt die Strukturkategorie fest
$field->setLabel($this->i18n('search_it_settings_exclude_articles'));

$cats = rex_sql::factory()->getArray("select id, catname, path from rex_article where startarticle = 1 GROUP BY id ORDER BY path ASC");


$field = $form->addSelectField('exclude_category_ids', $value = null, ['class'=>'form-control selectpicker']);
$field->setAttribute('multiple', 'multiple');
$field->setLabel($this->i18n('search_it_settings_exclude_categories'));
$select = $field->getSelect();

foreach ($cats as $cat) {
    $select->addOption($cat['path'] ." ". $cat['catname'], $cat['id']);
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
