<?php


$addon = rex_addon::get('search_it');
$form = rex_config_form::factory($addon->name);

$form->addFieldset($this->i18n('search_it_settings_fileext_header'));

$field = $form->addTextField('fileextensions');
$field->setLabel($this->i18n('search_it_settings_fileext_label'));

$field = $form->addTextareaField('indexfolders');
$field->setLabel($this->i18n('search_it_settings_folders_label'));

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', $addon->i18n('search_it_settings'), false);
$fragment->setVar('body', $form->get(), false);
echo $fragment->parse('core/page/section.php');

$form_name = $form->getName();
if (rex_post($form_name.'_save')) {
    rex_view::info($this->i18n('search_it_settings_saved'));
}
