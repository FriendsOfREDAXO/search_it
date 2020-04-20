<?php

$addon = rex_addon::get('search_it');
$form = rex_config_form::factory($addon->name);

$form->addFieldset($this->i18n('search_it_settings_title_index_article'));

/*
$field = $form->addCheckboxField('reindex_cols_onforms');
$field->setLabel("");
$field->addOption($this->i18n('search_it_settings_reindex_cols_onforms_label'), '1');
*/

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', $addon->i18n('search_it_settings'), false);
$fragment->setVar('body', $form->get(), false);
echo $fragment->parse('core/page/section.php');

$form_name = $form->getName();
if (rex_post($form_name.'_save')) {
    rex_view::info($this->i18n('search_it_settings_saved'));
}
