<?php

function search_it_doPlaintext($_ep){
    $subject = $_ep->getSubject();
    $subject = search_it_getPlaintext($subject, preg_replace('~\s+~ism', ' ', rex_plugin::get('search_it','plaintext')->getConfig('selectors')));
    return array('text' => $subject, 'process' => !empty(rex_plugin::get('search_it','plaintext')->getConfig('processparent')));
}


// require_once $dir.'/classes/class.simple_html_dom.inc.php';
function search_it_getPlaintext($_text,$_remove){

    $pt = rex_plugin::get('search_it','plaintext');
    foreach (explode(',', $pt->getConfig('order')) as $elem) {
        switch ($elem) {
            case 'selectors':
                // remove elements selected by css-selectors
                $html = new simple_html_dom();
                $html->load($_text);
                $html->remove($_remove);
                $html->load($html->outertext);
                $_text = $html->plaintext;
                break;

            case 'regex':
                // regex
                if (!empty($pt->getConfig('regex'))) {
                    $regex = [];
                    $replacement = [];
                    $odd = true;
                    foreach (explode("\n", $pt->getConfig('regex')) as $line) {
                        if ($line != '') {
                            if ($odd) {
                                $regex[] = trim($line);
                            } else {
                                $replacement[] = $line;
                            }
                            $odd = !$odd;
                        }
                    }
                    $_text = preg_replace($regex, $replacement, $_text);
                }
                break;

            case 'textile':
                // strip HTML-tags
                if (!empty($pt->getConfig('textile')) AND function_exists('rex_textile::parse')) {
                    $_text = rex_textile::parse($_text);
                }
                break;

            case 'striptags':
                // strip HTML-tags
                if (!empty($pt->getConfig('striptags'))) {
                    $_text = strip_tags($_text);
                }
                break;
        }
    }

    return $_text;
}
