<?php

$addon = rex_addon::get('search_it');
$form = rex_config_form::factory($addon->name);

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
