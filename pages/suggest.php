<?php

echo rex_view::title($this->i18n('title') . ' <small>(' . $this->getProperty('version') . ')</small>');

$content = '';
$buttons = '';

// Einstellungen speichern
if (rex_post('formsubmit', 'string') == '1') {

    $this->setConfig(rex_post('config', [
        ['autoComplete', 'int'],
        ['modus', 'string'],
        ['similarwordsmode', 'string'],
        ['maxSuggestion', 'string'],
        ['autoSubmitForm', 'int']
    ]));

    echo rex_view::success($this->i18n('search_it_autocomplete_config_saved'));

}


// autoComplete
$formElements = [];
$n = [];
$n['label'] = '<label for="autoComplete">' . $this->i18n('search_it_autocomplete_config_autoComplete') . '</label>';
$n['field'] = '<input type="checkbox" id="autoComplete" name="config[autoComplete]"' . (!empty($this->getConfig('autoComplete')) && $this->getConfig('autoComplete') == '1' ? ' checked="checked"' : '') . ' value="1" />';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/checkbox.php');

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


$file = rex_file::get(rex_path::addOn('search_it', 'docs/30_autocomplete.md'));
$body = rex_markdown::factory()->parse($file);
$fragment = new rex_fragment();
$fragment->setVar('title', $this->i18n('search_it_autocomplete_config_install'));
$fragment->setVar('body', $body, false);
$content = $fragment->parse('core/page/section.php');
echo $content;


$code = '<link rel="stylesheet" type="text/css" href="/' . substr(rex_url::addOnAssets('search_it', 'suggest.css'), 3) . '" media="screen" />
<script type="text/javascript" src="/' . substr(rex_url::addOnAssets('search_it', 'suggest.js'), 3) . '"></script>';
$code = preg_replace("#[\n]#", '', $code);

$content = '<div class="rexx-code"><code><pre>' . highlight_string($code, true) . '</pre></code></div>';

$fragment = new rex_fragment();
$fragment->setVar('title', $this->i18n('search_it_autocomplete_config_codesnippet'));
$fragment->setVar('body', $content, false);

echo $fragment->parse('core/page/section.php');

