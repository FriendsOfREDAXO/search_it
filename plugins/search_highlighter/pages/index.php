<?php

if (rex_post('sendit', 'boolean')) {

    $posted_config = rex_post('search_it_search_highlighter', [

        ['tag', 'string'],
        ['class', 'string'],
        ['inlineCSS', 'string'],
        ['stilEinbinden', 'bool'],
        ['stil', 'string'],
        ['stil1', 'string'],
        ['stil2', 'string'],
        ['stilEigen', 'string']

    ]);

    // do it
    $this->setConfig($posted_config);

    //tell it
    echo rex_view::success($this->i18n('search_it_settings_saved'));

}


$content = array();

foreach(array('b', 'span', 'strong', 'em', 'p', 'div') as $option){
    $options[] = array(
            'value' => $option,
            'selected' => !empty($this->getConfig('tag')) AND ($this->getConfig('tag') == $option),
            'name' => $option
    );
}

foreach(array('stil1', 'stil2', 'stilEigen') as $option){
    $optionsstil[] = array(
            'value' => $option,
            'selected' => !empty($this->getConfig('stil')) AND ($this->getConfig('stil') == $option),
            'name' => $option
    );
}


$content[] = search_it_getSettingsFormSection('stil_settings', $this->i18n('search_it_search_highlighter_site_title'),
            array(
                array(
                        'type' => 'select',
                        'id' => 'search_it_search_highlighter_tag',
                        'name' => 'search_it_search_highlighter[tag]',
                        'label' => $this->i18n('search_it_search_highlighter_tag'),
                        'options' => $options
                ),
                array(
                        'type' => 'string',
                        'id' => 'search_it_search_highlighter_class',
                        'name' => 'search_it_search_highlighter[class]',
                        'label' => $this->i18n('search_it_search_highlighter_class'),
                        'value' => !empty($this->getConfig('class')) ? $this->getConfig('class') : ''
                ),
                array(
                        'type' => 'string',
                        'id' => 'search_it_search_highlighter_inlineCSS',
                        'name' => 'search_it_search_highlighter[inlineCSS]',
                        'label' => $this->i18n('search_it_search_highlighter_inlineCSS'),
                        'value' => !empty($this->getConfig('inlineCSS')) ? $this->getConfig('inlineCSS') : ''
                ),
                array(
                        'type' => 'checkbox',
                        'id' => 'search_it_search_highlighter_stilEinbinden',
                        'name' => 'search_it_search_highlighter[stilEinbinden]',
                        'label' => $this->i18n('search_it_search_highlighter_stilEinbinden'),
                        'value' => '1',
                        'checked' => !empty($this->getConfig('stilEinbinden')) && $this->getConfig('stilEinbinden') == 1
                ),
                array(
                        'type' => 'select',
                        'id' => 'search_it_search_highlighter_stil',
                        'name' => 'search_it_search_highlighter[stil]',
                        'label' => $this->i18n('search_it_search_highlighter_stil'),
                        'options' => $optionsstil
                ),

                array(
                        'type' => 'hidden',
                        'id' => 'search_it_search_highlighter_stil1',
                        'name' => 'search_it_search_highlighter[stil1]',
                        'value' => !empty($this->getConfig('stil1')) ? $this->getConfig('stil1') : ''
                ),
                array(
                        'type' => 'hidden',
                        'id' => 'search_it_search_highlighter_stil2',
                        'name' => 'search_it_search_highlighter[stil2]',
                        'value' => !empty($this->getConfig('stil2')) ? $this->getConfig('stil2') : ''
                ),
                array(
                        'type' => 'text',
                        'id' => 'search_it_search_highlighter_stilEigen',
                        'name' => 'search_it_search_highlighter[stilEigen]',
                        'label' => $this->i18n('search_it_search_highlighter_stilEigen'),
                        'value' => $this->getConfig('stilEigen')
                )
            ),
            false );


//$content = array();
$content[] = '<div class="rex-form-row">
                <p class="rex-form-col-a rex-form-text">
                    F&uuml;r die Ausgabe wird eine modifizierte <a href="http://wiki.redaxo.de/index.php?n=R4.Search_it#example_result2" target="_blank">Search_it Ausgabemaske</a> ben&ouml;tigt.
                    <br />
                    Der Suchterm muss an den aufgerufenen Artikel &uuml;bergeben werden. Dies geschieht mit dem Querystring &quot;&amp;search_highlighter=&quot;
                </p>

                <div style="overflow: auto;">'.
    rex_string::highlight('<h4><a href="\'. ($url = htmlspecialchars($article->getUrl()) . \'&search_highlighter=\' . urlencode(rex_request(\'searchit\'))) .\'">\'.$article->getName().\'</a></h4>').'
                
                </div>
            </div>';


$content = implode( "\n", $content);

$formElements = [];
$n = [];
$n['field'] = '<button class="btn btn-save rex-form-aligned" type="submit" name="sendit" value="1" ' . rex::getAccesskey($this->i18n('search_it_settings_submitbutton'), 'save') . '>' . $this->i18n('search_it_settings_submitbutton') . '</button>';
$formElements[] = $n;
$fragment = new rex_fragment();
$fragment->setVar('flush', true);
$fragment->setVar('elements', $formElements, false);
$buttons = $fragment->parse('core/form/submit.php');

$fragment = new rex_fragment();
$fragment->setVar('title', $this->i18n('search_it_search_highlighter_site_title'),'');
$fragment->setVar('class', 'edit', false);
$fragment->setVar('body', $content, false);
$fragment->setVar('buttons', $buttons, false);

echo '<div class="rex-addon-output" id="search_it-form"><div class="rex-form">';
echo '<form method="post" action="'. rex_url::currentBackendPage() .'" id="search_it_search_highlighter_form">';
echo $fragment->parse('core/page/section.php');
echo '</form></div></div>';