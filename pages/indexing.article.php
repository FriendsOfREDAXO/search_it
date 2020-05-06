<?php

$addon = rex_addon::get('search_it');
$form = rex_config_form::factory($addon->name);

$field = $form->addCheckboxField('index_articles');
$field->setLabel("");
$field->addOption($this->i18n('search_it_indexing_article_opt'), '1');

$field = $form->addCheckboxField('indexoffline');
$field->setLabel("");
$field->addOption($this->i18n('search_it_settings_indexoffline'), '1');

$field = $form->addCheckboxField('automaticindex');
$field->setLabel("");
$field->addOption($this->i18n('search_it_settings_automaticindex_label'), '1');
$field->setNotice($this->i18n('search_it_settings_automaticindex_notice'));

$form->addFieldset($this->i18n('search_it_settings_article_blacklist'));

$field = $form->addSelectField('exclude_category_ids', $value = null, ['class'=>'form-control selectpicker']);
$field->setAttribute('multiple', 'multiple');
$field->setLabel($this->i18n('search_it_settings_exclude_categories'));
$field->setSelect(new rex_category_select(false, false, true, false));
$select = $field->getSelect();

$field = $form->addLinklistField('exclude_article_ids');
$field->setLabel($this->i18n('search_it_settings_exclude_articles'));

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', $addon->i18n('search_it_settings'), false);
$fragment->setVar('body', $form->get(), false);
echo $fragment->parse('core/page/section.php');

$form_name = $form->getName();
if (rex_post($form_name.'_save')) {
    rex_view::info($this->i18n('search_it_settings_saved'));
}
