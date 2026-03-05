<?php

namespace FriendsOfRedaxo\SearchIt\Helper;

use rex_fragment;

class FormBuilder
{
    public static function getSettingsFormSection(string $id = '', string $title = '&nbsp;', array $elements = [], $ownsection = 'info', bool $collapse = false): string
    {
        $return = '<fieldset id="' . $id . '">';
        $formElements = [];
        $fragment = new rex_fragment();

        foreach ($elements as $element) {
            if (count($element) == 0) {
                continue;
            }

            $n = [];

            switch ($element['type']) {
                case 'hidden':
                    $n['label'] = '';
                    $n['field'] = '<input type="hidden" name="' . $element['name'] . '" value="' . $element['value'] . '" />';
                    break;

                case 'string':
                    $n['label'] = '<label for="' . $element['id'] . '">' . $element['label'] . '</label>';
                    $n['field'] = '<input type="text" name="' . $element['name'] . '" class="form-control" id="' . $element['id'] . '" value="' . $element['value'] . '" />';
                    break;

                case 'password':
                    $n['label'] = '<label for="' . $element['id'] . '">' . $element['label'] . '</label>';
                    $n['field'] = '<input type="password" name="' . $element['name'] . '" class="form-control" id="' . $element['id'] . '" value="' . $element['value'] . '" />';
                    break;

                case 'text':
                    $n['label'] = '<label for="' . $element['id'] . '">' . $element['label'] . '</label>';
                    $n['field'] = '<textarea name="' . $element['name'] . '" class="form-control" id="' . $element['id'] . '" rows="10" cols="20">' . $element['value'] . '</textarea>';
                    break;

                case 'select':
                    $options = '';
                    foreach ($element['options'] as $option) {
                        $options .= '<option value="' . $option['value'] . '"' . ($option['selected'] ? ' selected="selected"' : '') . '>' . $option['name'] . '</option>';
                    }
                    $n['label'] = '<label for="' . $element['id'] . '">' . $element['label'] . '</label>';
                    $n['field'] = '<select class="form-control" id="' . $element['id'] . '" size="1" name="' . $element['name'] . '">' . $options . '</select>';
                    break;

                case 'multipleselect':
                    $options = '';
                    foreach ($element['options'] as $option) {
                        $optId = !empty($option['id']) ? ' id="' . $option['id'] . '"' : '';
                        $options .= '<option' . $optId . ' value="' . $option['value'] . '"' . ($option['selected'] ? ' selected="selected"' : '') . '>' . $option['name'] . '</option>';
                    }
                    $n['label'] = '<label for="' . $element['id'] . '">' . $element['label'] . '</label>';
                    $n['field'] = '<select id="' . $element['id'] . '" class="form-control" name="' . $element['name'] . '" multiple="multiple" size="' . $element['size'] . '"' . (!empty($element['disabled']) ? ' disabled="disabled"' : '') . '>' . $options . '</select>';
                    break;

                case 'multiplecheckboxes':
                    $checkboxes = '';
                    foreach ($element['options'] as $option) {
                        $formUnterElements = [];
                        $un = [];
                        $un['label'] = '<label for="' . $option['id'] . '">' . $option['name'] . '</label>';
                        $un['field'] = '<input type="checkbox" id="' . $option['id'] . '" name="' . $element['name'] . '" value="' . $option['value'] . '" ' . ($option['checked'] ? ' checked="checked"' : '') . ' />';
                        $un['highlight'] = $option['checked'];
                        $formUnterElements[] = $un;
                        $fragment = new rex_fragment();
                        $fragment->setVar('grouped', true);
                        $fragment->setVar('elements', $formUnterElements, false);
                        $checkboxes .= '<div class="col-xs-12">' . $fragment->parse('core/form/checkbox.php') . '</div>';
                    }
                    $n['label'] = '<label for="' . $element['id'] . '">' . $element['label'] . '</label>';
                    $n['field'] = '<div class="rex-form-col-a rex-form-text"><div class="form-group">' . $checkboxes . '</div></div>';
                    break;

                case 'radio':
                    foreach ($element['options'] as $option) {
                        $n['label'] = ' <label for="' . $option['id'] . '">' . $option['label'] . '</label>';
                        $n['field'] = '<input type="radio" name="' . $element['name'] . '" value="' . $option['value'] . '" class="rex-form-radio" id="' . $option['id'] . '"' . ($option['checked'] ? ' checked="checked"' : '') . ' />';
                        $formElements[] = $n;
                    }
                    break;

                case 'checkbox':
                    $formUnterElements = [];
                    $un = [];
                    $un['label'] = '<label for="' . $element['id'] . '">' . $element['label'] . '</label>';
                    $un['field'] = '<input type="checkbox" id="' . $element['id'] . '" name="' . $element['name'] . '" value="' . $element['value'] . '" ' . ($element['checked'] ? ' checked="checked"' : '') . ' />';
                    $un['highlight'] = $element['checked'];
                    $formUnterElements[] = $un;
                    $fragmentun = new rex_fragment();
                    $fragmentun->setVar('elements', $formUnterElements, false);
                    $n['field'] = $fragmentun->parse('core/form/checkbox.php');
                    break;

                case 'directoutput':
                    if (isset($element['where']) && $element['where'] != '') {
                        if ($element['where'] == 'left') {
                            $n['label'] = $element['output'];
                        } else {
                            $n['header'] = $element['output'];
                        }
                    } else {
                        $n['field'] = $element['output'];
                    }
                    break;
            }

            $formElements[] = $n;
        }
        $fragment->setVar('elements', $formElements, false);
        $return .= $fragment->parse('core/form/form.php') . '</fieldset>';

        if ($ownsection) {
            $fragment = new rex_fragment();
            $fragment->setVar('class', $ownsection);
            $fragment->setVar('title', $title, false);
            $fragment->setVar('body', $return, false);
            if ($collapse) {
                $fragment->setVar('collapse', true);
                $fragment->setVar('collapsed', true);
            }
            $return = $fragment->parse('core/page/section.php');
        }
        return $return;
    }
}
