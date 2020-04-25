<?php
switch($ajax) {
    case 'deleteindex':
        $delete = new search_it();
        $delete->deleteIndex();
        echo 1;
        break;

    case 'generate':
        // index column or article or file
        $search_it = new search_it();
        switch(rex_get('type')){
            case 'art':
                foreach($search_it->indexArticle($_id = intval(rex_get('id'))) as $langID => $article){

                    $msgtext = !is_null(rex_article::get($_id, $langID)) ? '<em><a target="_blank" href="'.rex_getUrl($_id, $langID).'">'. rex_escape(rex_article::get($_id, $langID)->getValue('name')).'</a></em> ' : '';
                    $msgtext .= '(ID=<strong>'.$_id.'</strong>,<strong>'.rex_clang::get($langID)->getName().'</strong>) ';

                    switch($article){
                        case SEARCH_IT_ART_ERROR:
                            echo '<p class="text-primary">'. $msgtext . $this->i18n('search_it_generate_article_socket_error').'</p>';
                            break;
                        case SEARCH_IT_ART_EXCLUDED:
                            echo '<p class="text-primary">'. $msgtext . $this->i18n('search_it_generate_article_excluded').'</p>';
                            break;
                        case SEARCH_IT_ART_404:
                            echo '<p class="text-primary">'. $msgtext . $this->i18n('search_it_generate_article_404_error').'</p>';
                            break;
                        case SEARCH_IT_ART_NOTOK:
                            echo '<p class="text-primary">'. $msgtext . $this->i18n('search_it_generate_article_http_error').'</p>';
                            break;
                        case SEARCH_IT_ART_IDNOTFOUND:
                            echo '<p class="text-info">'   . $msgtext . $this->i18n('search_it_generate_article_id_not_found').'</p>';
                            break;
                        case SEARCH_IT_ART_REDIRECT:
                            echo '<p class="text-primary">'. $msgtext . $this->i18n('search_it_generate_article_redirect').'</p>';
                            break;
                        case SEARCH_IT_ART_GENERATED:
                            echo '<p class="text-success">'. $msgtext . $this->i18n('search_it_generate_article_done').'</p>';
                            break;
                    }
                }
                break;

            case 'url':
				$url_sql = rex_sql::factory();
				$url_sql->setTable(search_it_getUrlAddOnTableName());
				$url_sql->setWhere("url_hash = '". rex_get('url_hash') ."'");
				if ($url_sql->select('url_hash, article_id, clang_id, profile_id, data_id, seo, url')) {
					if($url_sql->getValue('url_hash')) {
						foreach($search_it->indexUrl($url_sql->getValue('url_hash'), $url_sql->getValue('article_id'), $url_sql->getValue('clang_id'), $url_sql->getValue('profile_id'), $url_sql->getValue('data_id')) as $langID => $url) {
							$url_info = json_decode($url_sql->getValue('seo'), true);
							$msgtext = '<em><a target="_blank" href="'. $url_sql->getValue('url') .'">'. rex_escape($url_info["title"]).'</a></em> (URL Addon) ';

							switch($url){
								case SEARCH_IT_URL_ERROR:
									echo '<p class="text-primary">'. $msgtext . $this->i18n('search_it_generate_article_socket_error').'</p>';
									break;
								case SEARCH_IT_URL_EXCLUDED:
									echo '<p class="text-primary">'. $msgtext . $this->i18n('search_it_generate_url_excluded').'</p>';
									break;
								case SEARCH_IT_URL_404:
									echo '<p class="text-primary">'. $msgtext . $this->i18n('search_it_generate_article_404_error').'</p>';
									break;
								case SEARCH_IT_URL_NOTOK:
									echo '<p class="text-primary">'. $msgtext . $this->i18n('search_it_generate_article_http_error').'</p>';
									break;
								case SEARCH_IT_ART_IDNOTFOUND:
									echo '<p class="text-info">'   . $msgtext . $this->i18n('search_it_generate_article_id_not_found').'</p>';
									break;
								case SEARCH_IT_URL_REDIRECT:
									echo '<p class="text-primary">'. $msgtext . $this->i18n('search_it_generate_article_redirect').'</p>';
									break;
								case SEARCH_IT_URL_GENERATED:
									echo '<p class="text-success">'. $msgtext . $this->i18n('search_it_generate_article_done').'</p>';
									break;
							}
						}
					}
				}
                break;

            case 'col':
                if(false !== ($count = $search_it->indexColumn(rex_get('t'), rex_get('c'), false, false, rex_get('s'), rex_get('w')))) {
                    echo '<p class="text-warning"><em>`' . rex_get('t') . '`.`' . rex_get('c') . '` (' . rex_get('s') . ' - ' . (rex_get('s') + rex_get('w')) . ')</em> '. $this->i18n('search_it_generate_col_done',$count) . '</p>';
                } else {
                    echo '<p class="text-info">Error: <em>`' . rex_get('t') . '`.`' . rex_get('c') . '`</em>'. $this->i18n('search_it_generate_col_error',$count) . '</p>';
                }
                break;

            case 'file':
            case 'mediapool':
                $additionalOutput = '';
                if(rex_get('type') == 'file'){
                    $return = $search_it->indexFile(rex_get('name'));
                } else {
                    $return = $search_it->indexFile(rex_get('name'), false, false, rex_get('file_id'), rex_get('category_id'));
                    $additionalOutput = ' <em>(Mediapool)</em>';
                }

                switch($return){
                    case SEARCH_IT_FILE_FORBIDDEN_EXTENSION:
                        echo '<p class="text-info">'.$additionalOutput.' <strong>"'.rex_escape(rex_get('name')).'"</strong> '. $this->i18n('search_it_generate_media_forbidden_extension',$count) . '</p>';
                        break;

                    case SEARCH_IT_FILE_NOEXIST:
                        echo '<p class="text-info">'.$additionalOutput.' <strong>"'.rex_escape(rex_get('name')).'"</strong> '. $this->i18n('search_it_generate_media_doesnt_exist') . '</p>';
                        break;

                    case SEARCH_IT_FILE_XPDFERR_OPENSRC:
                        echo '<p class="text-info">'.$additionalOutput.': <strong>"'.rex_escape(rex_get('name')).'"</strong> '. $this->i18n('search_it_generate_media_error_pdf') . '</p>';
                        break;

                    case SEARCH_IT_FILE_XPDFERR_OPENDEST:
                        echo '<p class="text-info">'.$additionalOutput.': <strong>"'.rex_escape(rex_get('name')).'"</strong> '. $this->i18n('search_it_generate_media_error_output') . '</p>';
                        break;

                    case SEARCH_IT_FILE_XPDFERR_PERM:
                        echo '<p class="text-error">'.$additionalOutput.': <strong>"'.rex_escape(rex_get('name')).'"</strong> '. $this->i18n('search_it_generate_media_error_permissions') . '</p>';
                        break;

                    case SEARCH_IT_FILE_XPDFERR_OTHER:
                        echo '<p class="text-error">'.$additionalOutput.': <strong>"'.rex_escape(rex_get('name')).'"</strong> '. $this->i18n('search_it_generate_media_error_pdf2') . '</p>';
                        break;

                    case SEARCH_IT_FILE_EMPTY:
                        echo '<p class="text-error">'.$additionalOutput.' <strong>"'.rex_escape(rex_get('name')).'"</strong> '. $this->i18n('search_it_generate_media_empty') . '</p>';
                        break;

                    case SEARCH_IT_FILE_GENERATED:
                        echo '<p class="text-info">'.$additionalOutput.' <strong>"'.rex_escape(rex_get('name')).'"</strong> '. $this->i18n('search_it_generate_media_done') .'</p>';
                        break;
                }
                break;

            default:
                echo '<p class="alert-error">'. rex_i18n::rawMsg('search_it_generate_error') .'</p>';
        }
        break;

    case 'sample':
        header('Content-Type: text/html; charset=UTF-8');

        $sample = <<<EOT
Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.
Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi.
EOT;
        $search_it = new search_it();
        $search_it->setSearchString('"velit esse" accusam');
        $search_it->setHighlightType(rex_get('type'));
        $search_it->parseSearchString('"velit esse" accusam');

        if( rex_addon::get('search_it')->getConfig('highlight') == 'array' ){
            echo '<pre>';
            print_r($search_it->getHighlightedText($sample));
            echo '</pre>';
        } else {
            echo $search_it->getHighlightedText($sample);
        }
        break;

    case 'getdirs':

        $str = stripslashes(rex_request('startdirs','string','[]'));

        $startdirs = explode('","', substr($str, 2, -2));
        $dirs = [];
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
        echo '[' . implode(',', $dirs). ']';
        break;
}
