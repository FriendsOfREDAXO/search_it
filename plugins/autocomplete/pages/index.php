<?php

$content = '';
$buttons = '';

// Einstellungen speichern
if (rex_post('formsubmit', 'string') == '1') {
  $this->setConfig(rex_post('config', [
    ['modus','string'],
    ['similarwordsmode','string'],
    ['maxSuggestion','string'],
    ['REX_LINK_1','int'],
    ['autoSubmitForm','int']
  ]));
  
  echo rex_view::success($this->i18n('search_it_autocomplete_config_saved'));
  
}


if (rex::getUser()->isAdmin()) {
  $content = '';
  $searchtext = 'module:autocomplete_basic_output';
  
  $gm = rex_sql::factory();
  $gm->setQuery('select * from rex_module where output LIKE "%' . $searchtext . '%"');
  
  $module_id = 0;
  $module_name = '';
  foreach ($gm->getArray() as $module) {
    $module_id = $module['id'];
    $module_name = $module['name'];
  }
  
  $autocomplete_module_name = 'Suchhandler';
  
  if (rex_request('install-module', 'integer') == 1) {
    $output = rex_file::get(rex_path::plugin('search_it', 'autocomplete',  'module/module_output.inc'));
    
    $mi = rex_sql::factory();
    // $mi->debugsql = 1;
    $mi->setTable('rex_module');
    $mi->setValue('output', $output);
    
    if ($module_id == rex_request('module_id', 'integer', -1)) {
      $mi->setWhere('id="' . $module_id . '"');
      $mi->update();
      echo rex_view::success('Modul "' . $module_name . '" wurde aktualisiert');
    } else {
      $mi->setValue('name', $autocomplete_module_name);
      $mi->insert();
      $module_id = (int) $mi->getLastId();
      $module_name = $autocomplete_module_name;
      echo rex_view::success('Modul wurde angelegt unter "' . $autocomplete_module_name . '"');
    }
  }
  
  
  
  $searchtext = 'template:autocomplete_basic_output';
  
  $gm = rex_sql::factory();
  $gm->setQuery('select * from rex_template where content LIKE "%' . $searchtext . '%"');
  
  $template_id = 0;
  $template_name = '';
  foreach ($gm->getArray() as $template) {
    $template_id = $template['id'];
    $template_name = $template['name'];
  }
  
  $autocomplete_template_name = 'Suchhandler';
  
  if (rex_request('install-template', 'integer') == 1) {
    $output = rex_file::get(rex_path::plugin('search_it', 'autocomplete',  'template/template_content.inc'));
    
    $mi = rex_sql::factory();
    // $mi->debugsql = 1;
    $mi->setTable('rex_template');
    $mi->setValue('content', $output);
    $mi->setValue('attributes', '{"ctype":[],"modules":{"1":{"all":"1"}},"categories":{"all":"1"}}');
    $mi->setValue('active', 1);
    
    if ($template_id == rex_request('template_id', 'integer', -1)) {
      $mi->setWhere('id="' . $template_id . '"');
      $mi->update();
      echo rex_view::success('Template "' . $template_name . '" wurde aktualisiert');
    } else {
      $mi->setValue('name', $autocomplete_template_name);
      $mi->insert();
      $template_id = (int) $mi->getLastId();
      $template_name = $autocomplete_template_name;
      echo rex_view::success('Template wurde angelegt unter "' . $autocomplete_template_name . '"');
    }
  }
  
  
  
  
  
  
}



// modus
$formElements = [];
$n = [];
$n['label'] = '<label for="modus">' . $this->i18n('search_it_autocomplete_config_modus') . '</label>';
$select = new rex_select();
$select->setId('modus');
$select->setAttribute('class', 'form-control');
$select->setName('config[modus]');
$select->addOption('Keywords', 'keywords');
$select->addOption('Highlightedtext', 'highlightedtext');
$select->addOption('Artikelname', 'articlename');
$select->setSelected($this->getConfig('modus'));
$n['field'] = $select->get();
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/container.php');


// similarwordsmode
$formElements = [];
$n = [];
$n['label'] = '<label for="similarwordsmode">' . $this->i18n('search_it_autocomplete_config_similarwords_label') . '</label>';
$select = new rex_select();
$select->setId('modus');
$select->setAttribute('class', 'form-control');
$select->setName('config[similarwordsmode]');
$select->addOption($this->i18n('search_it_autocomplete_config_similarwords_none'), '0');
$select->addOption($this->i18n('search_it_autocomplete_config_similarwords_soundex'), '1');
$select->addOption($this->i18n('search_it_autocomplete_config_similarwords_metaphone'), '2');
$select->addOption($this->i18n('search_it_autocomplete_config_similarwords_cologne'), '3');
$select->addOption($this->i18n('search_it_autocomplete_config_similarwords_all'), '7');
$select->setSelected($this->getConfig('similarwordsmode'));
$n['field'] = $select->get();
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/container.php');


// maxSuggestion
$formElements = [];
$n = [];
$n['label'] = '<label for="maxSuggestion">' . $this->i18n('search_it_autocomplete_config_maxSuggestion') . '</label>';
$n['field'] = '<input class="form-control" type="text" id="maxSuggestion" name="config[maxSuggestion]" value="' . $this->getConfig('maxSuggestion') . '"/>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/container.php');

$formElements = [];
$n = [];
$n['label'] = '<label for="REX_LINK_1">' . $this->i18n('search_it_autocomplete_config_handlerId') . '</label>';
$n['field'] = '<div class="input-group"><input class="form-control" type="text" id="REX_LINK_1" name="config[REX_LINK_1]" value="' . $this->getConfig('REX_LINK_1') . '"/>
  <span class="input-group-btn">
    <a href="#" class="btn btn-popup" onclick="openLinkMap(\'REX_LINK_1\', \'&amp;clang=1&amp;category_id=1\');return false;" title="Link auswählen"><i class="rex-icon rex-icon-open-linkmap"></i></a>
    <a href="#" class="btn btn-popup" onclick="deleteREXLink(1);return false;" title="Ausgewählten Link löschen"><i class="rex-icon rex-icon-delete-link"></i></a>
   </span></div>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/container.php');

// autoSubmitForm
$formElements = [];
$n = [];
$n['label'] = '<label for="autoSubmitForm">' . $this->i18n('search_it_autocomplete_config_autoSubmitForm') . '</label>';
$n['field'] = '<input type="checkbox" id="autoSubmitForm" name="config[autoSubmitForm]"' . (!empty($this->getConfig('autoSubmitForm')) && $this->getConfig('autoSubmitForm') == '1' ? ' checked="checked"' : '') . ' value="1" />';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/checkbox.php');

// Save-Button
$formElements = [];
$n = [];
$n['field'] = '<button class="btn btn-save rex-form-aligned" type="submit" name="save" value="' . $this->i18n('search_it_autocomplete_config_save') . '">' . $this->i18n('search_it_autocomplete_config_save') . '</button>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$buttons = $fragment->parse('core/form/submit.php');
$buttons = '
<fieldset class="rex-form-action">
    ' . $buttons . '
</fieldset>
';


// Ausgabe Formular
$fragment = new rex_fragment();
$fragment->setVar('class', 'edit');
$fragment->setVar('title', $this->i18n('search_it_autocomplete_config'));
$fragment->setVar('body', $content, false);
$fragment->setVar('buttons', $buttons, false);
$output = $fragment->parse('core/page/section.php');

$output = '
<form action="' . rex_url::currentBackendPage() . '" method="post">
<input type="hidden" name="formsubmit" value="1" />
    ' . $output . '
</form>
';

echo $output;





$content = '<p>'.$this->i18n('search_it_autocomplete_install_description').'</p>';

if ($module_id > 0) {
  $content .= '<p><a class="btn btn-primary" href="index.php?page=search_it/autocomplete&amp;install-module=1&amp;module_id=' . $module_id . '" class="rex-button">' . $this->i18n('search_it_autocomplete_update_module', htmlspecialchars($module_name)) . '</a></p>';
} else {
  $content .= '<p><a class="btn btn-primary" href="index.php?page=search_it/autocomplete&amp;install-module=1" class="rex-button">' . $this->i18n('search_it_autocomplete_install_module', $autocomplete_module_name) . '</a></p>';
}


if ($template_id > 0) {
  $content .= '<p><a class="btn btn-primary" href="index.php?page=search_it/autocomplete&amp;install-template=1&amp;template_id=' . $template_id . '" class="rex-button">' . $this->i18n('search_it_autocomplete_update_template', htmlspecialchars($template_id)) . '</a></p>';
} else {
  $content .= '<p><a class="btn btn-primary" href="index.php?page=search_it/autocomplete&amp;install-template=1" class="rex-button">' . $this->i18n('search_it_autocomplete_install_template', $autocomplete_template_name) . '</a></p>';
}

$fragment = new rex_fragment();
$fragment->setVar('title', $this->i18n('search_it_autocomplete_install'), false);
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');




$file = rex_file::get(rex_path::plugin('search_it', 'autocomplete','README.md'));
$body = rex_markdown::factory()->parse($file);
$fragment = new rex_fragment();
$fragment->setVar('title', $this->i18n('search_it_autocomplete_config_install'));
$fragment->setVar('body', $body, false);
$content = $fragment->parse('core/page/section.php');
echo $content;


$code = file_get_contents($this->getPath('code/template.php'));

// replace ###HANDLEURL### with article url
if (is_numeric($this->getConfig('REX_LINK_1'))) {
  
  if(substr(rex::getServer(), -1) == '/')
    $serverHost = substr(rex::getServer(), 0, -1);
    
    $code = str_replace('###SERVER###', $serverHost, $code);
    $code = str_replace('###HANDLEID###', $this->getConfig('REX_LINK_1'), $code);
}


$content = '<div class="rexx-code"><code><pre>' . highlight_string($code, true)  . '</pre></code></div>';

$fragment = new rex_fragment();
$fragment->setVar('title', $this->i18n('search_it_autocomplete_config_codesnippet'));
$fragment->setVar('body', $content, false);

echo $fragment->parse('core/page/section.php');

