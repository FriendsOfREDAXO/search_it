<?php
switch($ajax) {
    case 'deleteindex':
        $delete = new search_it();
        $delete->deleteIndex();
        echo 1;
        break;

    case 'generate':
        // index column or article
        $search_it = new search_it();
        switch($_GET['type']){
            case 'art':
                foreach($search_it->indexArticle($_id = intval($_GET['id'])) as $langID => $article){
                    switch($article){
                        case SEARCH_IT_ART_EXCLUDED:
                            echo '<p class="text-primary">Article (ID=<strong>'.$_id.'</strong>,<strong>'.$langID.'</strong>) is excluded</p>';
                            break;
                        case SEARCH_IT_ART_IDNOTFOUND:
                            echo '<p class="text-info">Article (ID=<strong>'.$_id.'</strong>,<strong>'.$langID.'</strong>) not found</p>';
                            break;
                        case SEARCH_IT_ART_REDIRECT:
                            echo '<p class="text-primary">Article (ID=<strong>'.$_id.'</strong>,<strong>'.$langID.'</strong>) is excluded because of a redirect</p>';
                            break;
                        case SEARCH_IT_ART_GENERATED:
                            $article = new rex_article_content($_id, $langID);
                            echo '<p class="text-warning">Done: Article <em>"'.htmlspecialchars($article->getValue('name')).'"</em> (ID=<strong>'.$_id.'</strong>,<strong>'.rex_clang::get($langID)->getName().'</strong>)</p>';
                            break;
                    }
                }
                break;

            case 'col':
                if(false !== ($count = $search_it->indexColumn($_GET['t'], $_GET['c'], false, false, $_GET['s'], $_GET['w'])))
                    echo '<p class="text-warning">Done: <em>`'.$_GET['t'].'`.`'.$_GET['c'].'` ('.$_GET['s'].' - '.($_GET['s'] + $_GET['w']).')</em> (<strong>'.$count.'</strong> row(s) indexed)</p>';
                else
                    echo '<p class="text-info">Error: <em>`'.$_GET['t'].'`.`'.$_GET['c'].'`</em> not found</p>';
                break;

            case 'file':
            case 'mediapool':
                $additionalOutput = '';
                if($_GET['type'] == 'file'){
                    $return = $search_it->indexFile($_GET['name']);
                } else {
                    $return = $search_it->indexFile(rex_path::media($_GET['name']), false, false, $_GET['file_id'], $_GET['category_id']);
                    $additionalOutput = ' <em>(Mediapool)</em>';
                }

                switch($return){
                    case SEARCH_IT_FILE_FORBIDDEN_EXTENSION:
                        echo '<p class="text-info">File'.$additionalOutput.' <strong>"'.htmlspecialchars($_GET['name']).'"</strong> has a forbidden filename extension.</p>';
                        break;

                    case SEARCH_IT_FILE_NOEXIST:
                        echo '<p class="text-info">File'.$additionalOutput.' <strong>"'.htmlspecialchars($_GET['name']).'"</strong> does not exist.</p>';
                        break;

                    case SEARCH_IT_FILE_XPDFERR_OPENSRC:
                        echo '<p class="text-info">XPDF-error: Error opening a PDF file. File'.$additionalOutput.': <strong>"'.htmlspecialchars($_GET['name']).'"</strong>.</p>';
                        break;

                    case SEARCH_IT_FILE_XPDFERR_OPENDEST:
                        echo '<p class="text-info">XPDF-error: Error opening an output file. File'.$additionalOutput.': <strong>"'.htmlspecialchars($_GET['name']).'"</strong>.</p>';
                        break;

                    case SEARCH_IT_FILE_XPDFERR_PERM:
                        echo '<p class="text-error">XPDF-error: Error related to PDF permissions. File'.$additionalOutput.': <strong>"'.htmlspecialchars($_GET['name']).'"</strong>.</p>';
                        break;

                    case SEARCH_IT_FILE_XPDFERR_OTHER:
                        echo '<p class="text-error">XPDF-error: Other error. File'.$additionalOutput.': <strong>"'.htmlspecialchars($_GET['name']).'"</strong>.</p>';
                        break;

                    case SEARCH_IT_FILE_EMPTY:
                        echo '<p class="text-error">File'.$additionalOutput.' <strong>"'.htmlspecialchars($_GET['name']).'"</strong> is empty or could not be extracted.</p>';
                        break;

                    case SEARCH_IT_FILE_GENERATED:
                        echo '<p class="text-info">Done: File'.$additionalOutput.' <strong>"'.htmlspecialchars($_GET['name']).'"</strong>';
                        break;
                }
                break;

            default:
                echo '<p class="alert-error">Error: <em>Wrong request parameters!</em></p>';
        }
        break;

    case 'sample':
        header('Content-Type: text/html; charset=UTF-8');

        $sample = <<<EOT
Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.
Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi.
EOT;
        $search_it = new search_it();
        $search_it->searchString = '"velit esse" accusam';
        $search_it->setHighlightType($_GET['type']);
        $search_it->parseSearchString('"velit esse" accusam');

        if($search_it->highlightType == 'array'){
            echo '<pre style="font-size:1.2em;display:inline;">';
            print_r($search_it->getHighlightedText($sample));
            echo '</pre>';
        } else {
            echo $search_it->getHighlightedText($sample);
        }
        break;

    case 'getdirs':
        echo '[';
        $str = stripslashes(rex_request('startdirs','string','[]'));

        $startdirs = explode('","', substr($str, 2, -2));
        $dirs = array();
        if(!empty($startdirs)){

            if(is_array($startdirs)){
                foreach($startdirs as $dir){
                    foreach(search_it_getDirs(str_replace('\\"', '"', $dir)) as $absolute => $relative){
                        $dirs[] = '"'.addcslashes($relative, '"/\\').'"';
                    }
                }
            } else {
                foreach(search_it_getDirs(str_replace('\\"', '"', $startdirs)) as $absolute => $relative) {
                    $dirs[] = '"'.addcslashes($relative, '"/\\').'"';
                }
            }
        } else {
            foreach(search_it_getDirs() as $absolute => $relative) {
                $dirs[] = '"'.addcslashes($relative, '"/\\').'"';
            }
        }

        echo implode(',', $dirs);

        echo ']';
        break;
}