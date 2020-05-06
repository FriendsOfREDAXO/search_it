<?php


$addon = rex_addon::get('search_it');
$form = rex_config_form::factory($addon->name);

$field = $form->addCheckboxField('indexmediapool');
$field->setLabel("");
$field->addOption($this->i18n('search_it_settings_file_mediapool'), '1');

$field = $form->addCheckboxField('indexmediapool_meta');
$field->setLabel("");
$field->addOption($this->i18n('search_it_settings_file_mediapool_meta'), '1');

$field = $form->addTextField('media_extensions');
$field->setLabel($this->i18n('search_it_settings_media_fileext_label'));

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', $addon->i18n('search_it_indexing_mediapool'), false);
$fragment->setVar('body', $form->get(), false);
echo $fragment->parse('core/page/section.php');

$form_name = $form->getName();
if (rex_post($form_name.'_save')) {
    rex_view::info($this->i18n('search_it_settings_saved'));
}
