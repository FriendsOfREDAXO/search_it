<?php

$addon = rex_addon::get('search_it');
$form = rex_config_form::factory($addon->name);

$form->addFieldset($this->i18n('search_it_settings_modi_header'));

$field = $form->addSelectField('logicalmode', $value = null, ['class'=>'form-control selectpicker']);
$field->setLabel($this->i18n('search_it_settings_logicalmode'));
$select = $field->getSelect();
$select->addOption($this->i18n('search_it_settings_logicalmode_and'), 'and');
$select->addOption($this->i18n('search_it_settings_logicalmode_or'), 'or');

$field = $form->addSelectField('textmode', $value = null, ['class'=>'form-control selectpicker']);
$field->setLabel($this->i18n('search_it_settings_textmode'));
$select = $field->getSelect();
$select->addOption($this->i18n('search_it_settings_textmode_plain'), 'plain');
$select->addOption($this->i18n('search_it_settings_textmode_html'), 'html');
$select->addOption($this->i18n('search_it_settings_textmode_both'), 'both');

$field = $form->addSelectField('similarwordsmode', $value = null, ['class'=>'form-control selectpicker']);
$field->setLabel($this->i18n('search_it_settings_similarwords_label'));
$select = $field->getSelect();
$select->addOption($this->i18n('search_it_settings_similarwords_none'), SEARCH_IT_SIMILARWORDS_NONE);
$select->addOption($this->i18n('search_it_settings_similarwords_soundex'), SEARCH_IT_SIMILARWORDS_SOUNDEX);
$select->addOption($this->i18n('search_it_settings_similarwords_metaphone'), SEARCH_IT_SIMILARWORDS_METAPHONE);
$select->addOption($this->i18n('search_it_settings_similarwords_cologne'), SEARCH_IT_SIMILARWORDS_COLOGNEPHONE);
$select->addOption($this->i18n('search_it_settings_similarwords_all'), SEARCH_IT_SIMILARWORDS_ALL);


$field = $form->addCheckboxField('similarwords_permanent');
$field->setLabel($this->i18n('search_it_settings_similarwords_permanent'));
$field->addOption($this->i18n('search_it_settings_similarwords_permanent'), '1');

$field = $form->addSelectField('searchmode', $value = null, ['class'=>'form-control selectpicker']);
$field->setLabel($this->i18n('search_it_settings_searchmode'));
$select = $field->getSelect();
$select->addOption($this->i18n('search_it_settings_searchmode_like'), 'like');
$select->addOption($this->i18n('search_it_settings_searchmode_match'), 'match');
$select->addOption('Vegetarisch', 'vegetarisch');

$form->addFieldset($this->i18n('search_it_settings_title_indexmode'));

$field = $form->addCheckboxField('indexoffline');
$field->setLabel("");
$field->addOption($this->i18n('search_it_settings_indexoffline'), '1');

$field = $form->addCheckboxField('automaticindex');
$field->setLabel("");
$field->addOption($this->i18n('search_it_settings_automaticindex_label'), '1');
/*
$field = $form->addCheckboxField('reindex_cols_onforms');
$field->setLabel("");
$field->addOption($this->i18n('search_it_settings_reindex_cols_onforms_label'), '1');
*/
$field = $form->addCheckboxField('index_url_addon');
$field->setLabel("");
$field->addOption($this->i18n('search_it_settings_index_url_addon_label'), '1');

$form->addFieldset($this->i18n('search_it_settings_http_authbasic'));

$field = $form->addTextField('htaccess_user');
$field->setLabel($this->i18n('search_it_settings_htaccess_user'));

$field = $form->addTextField('htaccess_pass');
$field->setLabel($this->i18n('search_it_settings_htaccess_pass'));
$field->setNotice($this->i18n('search_it_settings_http_auth_desc'));

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', $addon->i18n('search_it_settings'), false);
$fragment->setVar('body', $form->get(), false);
echo $fragment->parse('core/page/section.php');

$form_name = $form->getName();
if (rex_post($form_name.'_save')) {
    rex_view::info($this->i18n('search_it_settings_saved'));
}
