<?php

$addon = rex_addon::get('search_it');
$form = rex_config_form::factory($addon->name);

$form->addFieldset($this->i18n('search_it_settings_search_result'));

$field = $form->addTextField('surroundtags[0]', '', ["class" => "form-control"]);
$field->setLabel($this->i18n('search_it_settings_surroundtags_start'));

$field = $form->addTextField('surroundtags[1]', '', ["class" => "form-control"]);
$field->setLabel($this->i18n('search_it_settings_surroundtags_end'));

$field = $form->addHiddenField('limit[0]', 0);

$field = $form->addTextField('limit[1]', '', ["class" => "form-control"]);
$field->setLabel($this->i18n('search_it_settings_limit'));

$field = $form->addTextField('maxteaserchars', '', ["class" => "form-control"]);
$field->setLabel($this->i18n('search_it_settings_maxteaserchars'));

$field = $form->addTextField('maxhighlightchars', '', ["class" => "form-control"]);
$field->setLabel($this->i18n('search_it_settings_maxhighlightchars'));

$field = $form->addSelectField('highlight', $value = null, ['class'=>'form-control selectpicker']);
$field->setLabel($this->i18n('search_it_settings_highlight_label'));
$select = $field->getSelect();
$select->addOption($this->i18n('search_it_settings_highlight_sentence'), "sentence");
$select->addOption($this->i18n('search_it_settings_highlight_paragraph'), "paragraph");
$select->addOption($this->i18n('search_it_settings_highlight_surroundtext'), "surroundtext");
$select->addOption($this->i18n('search_it_settings_highlight_surroundtextsingle'), "surroundtextsingle");
$select->addOption($this->i18n('search_it_settings_highlight_teaser'), "teaser");
$select->addOption($this->i18n('search_it_settings_highlight_array'), "array");

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', $addon->i18n('search_it_settings'), false);
$fragment->setVar('body', $form->get(), false);
echo $fragment->parse('core/page/section.php');

$form_name = $form->getName();
if (rex_post($form_name.'_save')) {
    rex_view::info($this->i18n('search_it_settings_saved'));
}

/* Todo: Sample einfügen */

$sample = "Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.
Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi.";

$sampleoutput = '<div class="rex-form-row"><div id="search_it_sample_wrapper">
        <h5 class="rex-form-text">'.$this->i18n('search_it_settings_highlight_sample').':<strong>"velit esse" accusam</strong></h5>
        <div id="search_it_sample">';
$search_it = new search_it();
$search_it->setSearchString('"velit esse" accusam');
$search_it->parseSearchString('"velit esse" accusam');
if ($this->getConfig('highlight') == 'array') {
    $sampleoutput .= '<pre>';
    $sampleoutput .= print_r($search_it->getHighlightedText($sample), true);
    $sampleoutput .= '</pre>';
} else {
    $sampleoutput .= $search_it->getHighlightedText($sample);
}
$sampleoutput .= '</div></div></div>';
