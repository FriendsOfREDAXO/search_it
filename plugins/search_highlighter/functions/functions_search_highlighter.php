<?php

function search_it_search_highlighter_output($_ep){

        $subject = $_ep->getSubject();

        $sh = rex_plugin::get('search_it','search_highlighter');

        $suchbegriffe = rex_request('search_highlighter', 'string', '');
        $ausgabeAnfang = '';
        $ausgabeEnde = '';

        if (!empty($sh->getConfig('stilEinbinden'))){
            $subject = str_replace('</head>', '<link rel="stylesheet" type="text/css" href="' . rex_url::frontendController( array('stil'=>urlencode($sh->getConfig('stil')))) . '" media="screen" />'."\n".'</head>', $subject);
        }

        $ausgabeAnfang = '<' . $sh->getConfig('tag');
        $ausgabeAnfang .= (!empty($sh->getConfig('class'))) ? ' class="' . $sh->getConfig('class').'"' : '';
        $ausgabeAnfang .= (!empty($sh->getConfig('inlineCSS'))) ? ' style="' . $sh->getConfig('inlineCSS') . '"' : '';
        $ausgabeAnfang .= '>';

        $ausgabeEnde = '</' . $sh->getConfig('tag') . '>';

        $tags = array($ausgabeAnfang, $ausgabeEnde);

        $subject = search_it_search_highlighter_getHighlightedText($subject, $suchbegriffe, $tags);

        return $subject;

}


/*
function rex_search_search_highlighter_getHighlightedText($subject, $begriffe, $tags){

    $matches = preg_split(rex_search_search_highlighter_encodeRegex('~(\s)~'), $begriffe);

    //nur innerhalb des bodys suchen
    $vorkommenBody = stripos($subject, '<body');
    $body1 = substr($subject, 0, $vorkommenBody);
    $endeBody = stripos($subject, '</body');
    $body2 = substr($subject, $vorkommenBody, $endeBody);



    $keyword = "";
    foreach ($matches as $match){
        $match = preg_replace('([^\w\d\+\-\_\.\,])', '', $match);

        if (strlen($match) <= 2) { continue; }

        preg_match_all('/' . $match . '/i', $body2, $keywords, PREG_SET_ORDER);
        foreach ($keywords as $keyword){
            $body2 = preg_replace('/' . $keyword[0] . '/', $tags[0] . $keyword[0] . $tags[1], $body2);
        }
    }

    return $body1 . $body2;
}
*/
function search_it_search_highlighter_getHighlightedText($_subject, $_searchString, $_tags){
    preg_match_all('~(?:(\+*)"([^"]*)")|(?:(\+*)(\S+))~is', $_searchString, $matches, PREG_SET_ORDER);

    $searchterms = array();
    foreach ($matches as $match) {
        if (count($match) == 5) {
            // words without double quotes (foo)
            $word = $match[4];
        } elseif (!empty($match[2])) {
            // words with double quotes ("foo bar")
            $word = $match[2];
        } else {
            continue;
        }
        $searchterms[] = preg_quote($word, '~');
    }

    return preg_replace('~(?<!\<)(' . implode('|', $searchterms) . ')(?![^<]*\>)~ims', $_tags[0] . '$1' . $_tags[1], $_subject);
}

function search_it_search_highlighter_encodeRegex($_regex){
    return utf8_encode($_regex . 'u');
}

function search_it_search_highlighter_stil_css($_stil){

    //ob_clean();
    $charset = "utf-8";
    header("Content-Type: text/css; charset=" . $charset);


    $sh = rex_plugin::get('search_it','search_highlighter');

    //css
    echo '.search_it_search_highlighter {';
    switch ($_stil){

        case 'stil2':
            echo $sh->getConfig('stil2');
            break;

        case 'stilEigen':
            echo $sh->getConfig('stilEigen');
            break;

        case 'stil1':
        default:
            echo $sh->getConfig('stil1');
            break;
    }

    echo '}';
    exit();
}
?>
