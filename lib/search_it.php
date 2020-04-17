<?php
/**
 * Class search_it
 *
 * This class is still being tested.
 * Please report errors at http://forum.redaxo.de.
 *
 * @author    Robert Rupf/Norbert Micheel
 * @package   search_it
 */


/**
 * @package   search_it
 */
class search_it
{

    private $searchString = '';
    private $hashMe = '';
    private $clang = false;
    private $ellipsis;
    private $tablePrefix;
    private $tempTablePrefix;
    private $urlAddOnTableName;
    private $significantCharacterCount = 3;
    private $stopwords = [];
    private $errormessages = '';

    private $ci = true;
    private $where = '';
    private $groupBy = true;
    private $order = ['RELEVANCE_SEARCH_IT' => 'DESC'];

    private $searchArray = [];
    private $searchHtmlEntities = false;
    private $cache = true;
    private $cachedArray = [];
    private $searchInIDs = [];
    private $searchAllArticlesAnyway = false;
    private $whitelist = [];


    /* config values */
    private $logicalMode = ' AND ';
    private $textMode = 'plain';
    private $similarwords = false;
    private $similarwordsMode = 0;
    private $similarwordsPermanent = false;
    private $searchMode = 'like';

    private $surroundTags = array('<mark>', '</mark>');
    private $limit = array(0, 10);
    private $maxTeaserChars = 200;
    private $maxHighlightedTextChars = 100;
    private $highlightType = 'surroundtext';

    private $includeColumns = [];
    private $fileExtensions = [];
    private $indexMediapool = false;
    private $fileDirectories = [];

    private $blacklist = [];
    private $blacklisted = [];
    private $excludeIDs = [];


    function __construct($_clang = false, $_loadSettings = true, $_useStopwords = true)
    {

        if ($_loadSettings) {

            $this->setCaseInsensitive(rex_addon::get('search_it')->getConfig('ci'));

            $this->setLogicalMode(rex_addon::get('search_it')->getConfig('logicalmode'));
            $this->setTextMode(rex_addon::get('search_it')->getConfig('textmode'));
            $this->similarwordsMode = intval(rex_addon::get('search_it')->getConfig('similarwordsmode'));
            $this->similarwords = (bool)$this->similarwordsMode;
            $this->similarwordsPermanent = rex_addon::get('search_it')->getConfig('similarwords_permanent');
            $this->setSearchMode(rex_addon::get('search_it')->getConfig('searchmode'));

            $this->setSurroundTags(rex_addon::get('search_it')->getConfig('surroundtags'));
            $this->setLimit(rex_addon::get('search_it')->getConfig('limit'));
            $this->setMaxTeaserChars(rex_addon::get('search_it')->getConfig('maxteaserchars'));
            if (rex_addon::get('search_it')->getConfig('maxhighlightchars') > 0) {
                $this->setMaxHighlightedTextChars(rex_addon::get('search_it')->getConfig('maxhighlightchars'));
            }
            $this->setHighlightType(rex_addon::get('search_it')->getConfig('highlight'));

            $this->includeColumns = is_array(rex_addon::get('search_it')->getConfig('include')) ? rex_addon::get('search_it')->getConfig('include') : [];
            $this->fileExtensions = rex_addon::get('search_it')->getConfig('fileextensions');
            $this->indexMediapool = rex_addon::get('search_it')->getConfig('indexmediapool');
            $this->fileDirectories = is_array(rex_addon::get('search_it')->getConfig('indexfolders')) ? rex_addon::get('search_it')->getConfig('indexfolders') : [];

            $this->setBlacklist(is_array(rex_addon::get('search_it')->getConfig('blacklist')) ? rex_addon::get('search_it')->getConfig('blacklist') : []);
            $this->setExcludeIDs(is_array(rex_addon::get('search_it')->getConfig('exclude_article_ids')) ? rex_addon::get('search_it')->getConfig('exclude_article_ids') : []);
            if (is_array(rex_addon::get('search_it')->getConfig('exclude_category_ids'))) {
                $ids = [];
                foreach (rex_addon::get('search_it')->getConfig('exclude_category_ids') as $catID) {
                    foreach (search_it_getArticles(array($catID)) as $id => $name) {
                        $ids[] = $id;
                    }
                    $this->setExcludeIDs($ids);
                }
            }
        }

        $this->setClang($_clang);
        $this->tablePrefix = rex::getTablePrefix();
        $this->tempTablePrefix = rex::getTablePrefix().rex::getTempPrefix();
        $this->urlAddOnTableName = search_it_getUrlAddOnTableName();

        $this->ellipsis = rex_i18n::msg('search_it_ellipsis');

        // german stopwords
        if ($_useStopwords) {
            include rex_path::addon('search_it', '/lang/stopwords.php');
            $this->stopwords = $german_stopwords;
        }
    }

    /* indexing */
    /**
     * Generates the full index at once.
     */
    public function generateIndex()
    {
        // delete old index and cache
        $this->deleteIndex();
        $this->deleteCache();

		// index articles
        $global_return = 0;
        $art_sql = rex_sql::factory();
        $art_sql->setTable($this->tablePrefix . 'article');
        if ($art_sql->select('id,clang_id')) {
            foreach ($art_sql->getArray() as $art) {
                $returns = $this->indexArticle($art['id'], $art['clang_id']);
                foreach ( $returns as $return ) {
                    if ($return > 3 ) { $global_return += $return; }
                }
			}
        }

		// index url 2 addon URLs
		if(rex_addon::get('search_it')->getConfig('index_url_addon') && search_it_isUrlAddOnAvailable()) {
			$url_sql = rex_sql::factory();
			$url_sql->setTable($this->urlAddOnTableName);
			if ($url_sql->select('url_hash, article_id, clang_id, profile_id, data_id')) {
				foreach ($url_sql->getArray() as $url) {
					$returns = $this->indexUrl($url['url_hash'], $url['article_id'], $url['clang_id'], $url['profile_id'], $url['data_id']);
					foreach ( $returns as $return ) {
						if ($return > 3 ) { $global_return += $return; }
					}
				}
			}
		}

		// index columns
        foreach ($this->includeColumns as $table => $columnArray) {
            foreach ($columnArray as $column) {
                $this->indexColumn($table, $column);
            }
        }

        // index mediapool
        if ($this->indexMediapool) {
            $mediaSQL = rex_sql::factory();
            $mediaSQL->setTable($this->tablePrefix . 'media');
            if ($mediaSQL->select('id, category_id, filename')) {
                foreach ($mediaSQL->getArray() as $file) {
                    $this->indexFile('media/' . $file['filename'], false, false, $file['id'], $file['category_id']);
                }
            }
        }

        // index files
        foreach ($this->fileDirectories as $dir) {
            foreach (search_it_getFiles($dir, $this->fileExtensions) as $filename) {
                //$filename is a full path with dir
                $this->indexFile(substr($filename, 1));
            }
        }

		return $global_return;
    }

    /**
     * Indexes a certain article.
     *
     * @param int $_id
     * @param mixed $_clang
     *
     * @return int
     */
    public function indexArticle($_id, $_clang = false)
    {

        if ($_clang === false) {
            $langs = rex_clang::getAll();
        } else {
            $langs = array(rex_clang::get($_clang));
        }

        $return = [];
        $keywords = [];
        foreach ($langs as $lang) {
            $langID = $lang->getId();

            if (in_array($_id, $this->excludeIDs)) {
                $return[$langID] = SEARCH_IT_ART_EXCLUDED;
                continue;
            }

            //rex_clang::setCurrentId($langID);
            $delete = rex_sql::factory();
            $where = sprintf("ftable = '%s' AND fid = %d AND clang = %d", $this->tablePrefix . 'article', $_id, $langID);

            // delete from cache
            $select = rex_sql::factory();
            $select->setTable($this->tempTablePrefix . 'search_it_index');
            $select->setWhere($where);
            $select->select('id');

            $indexIds = [];
            foreach ($select->getArray() as $result) {
                $indexIds[] = $result['id'];
            }
            $this->deleteCache($indexIds);

            // delete old
            $delete->setTable($this->tempTablePrefix . 'search_it_index');
            $delete->setWhere($where);
            $delete->delete();

            // index article
            $article = rex_article::get(intval($_id), $langID);
            if ( is_null($article)) {
                $return[$langID] = SEARCH_IT_ART_IDNOTFOUND;
                continue;
            }

            if (is_object($article) AND ($article->isOnline() OR rex_addon::get('search_it')->getConfig('indexoffline')) AND $_id != 0
                AND ($_id != rex_article::getNotfoundArticleId() OR $_id == rex_article::getSiteStartArticleId())  ) {


                 try {
                        $scanurl = rtrim(rex::getServer(), "/") . '/' . ltrim(str_replace(array('../', './'), '', rex_getUrl($_id, $langID,array('search_it_build_index'=>'do-it'),'&')),"/");
                        if(rex_addon::get("yrewrite") && rex_addon::get("yrewrite")->isAvailable()) {
                            $scanurl = rex_yrewrite::getFullUrlByArticleId($_id, $langID, array('search_it_build_index' => 'do-it-with-yrewrite'), '&');
                        }

                        $files_socket = rex_socket::factoryURL($scanurl);
                        if (rex_addon::get('search_it')->getConfig('htaccess_user') != '' && rex_addon::get('search_it')->getConfig('htaccess_pass') != '') {
                            $files_socket->addBasicAuthorization(rex_addon::get('search_it')->getConfig('htaccess_user'),rex_addon::get('search_it')->getConfig('htaccess_pass'));
                        }
                        $response = $files_socket->doGet();

                        $redircount = 0;
                        while ($response->isRedirection() && $redircount < 3) {

                            $lastscanurl = $scanurl;
                            $scanurl = str_replace(array('../', './'), '/',$response->getHeader('location'));

                            if (strpos($scanurl,'//') === false ) {
                                $parts = parse_url($lastscanurl);
                                if ( isset($parts['scheme']) && isset($parts['host']) ) {
                                    $scanurl = $parts['scheme'] . '://' . $parts['host'] . $scanurl;
                                }
                            }
                            $scanurl .= ( strpos($scanurl,'?') !== false ? '&' : '?').'search_it_build_index=redirect';
                            //rex_logger::factory()->log('Warning','Redirect von '.$lastscanurl.' zu '.$scanurl.', '.$response->getHeader());

                            $files_socket = rex_socket::factoryURL($scanurl);
                            if (rex_addon::get('search_it')->getConfig('htaccess_user') != '' && rex_addon::get('search_it')->getConfig('htaccess_pass') != '') {
                                $files_socket->addBasicAuthorization(rex_addon::get('search_it')->getConfig('htaccess_user'),rex_addon::get('search_it')->getConfig('htaccess_pass'));
                            }
                            $response = $files_socket->doGet();
                            $redircount++;
                        }

                        if ($response->isOk()) {
                            $articleText = $response->getBody();
                        } else {
                            $articleText = '';
                            !is_null($response) ? $response_text = $response->getStatusCode() . ' - ' . $response->getStatusMessage() : $response_text = '';
                            if ( $response->isRedirection() ) {
                                $return[$langID] = SEARCH_IT_ART_REDIRECT;
                                $response_text = rex_i18n::msg('search_it_generate_article_redirect');
                                rex_logger::factory()->log( 'Warning', rex_i18n::msg('search_it_generate_article_http_error') .' '. $scanurl . '<br>' . $response_text );
                            } else if ( $response->getStatusCode() == '404' ) {
                                $return[$langID] = SEARCH_IT_ART_404;
                                rex_logger::factory()->log( 'Warning', rex_i18n::msg('search_it_generate_article_404_error') .' '. $scanurl . '<br>' . $response_text );
                            } else {
                                rex_logger::factory()->log( 'Warning', rex_i18n::msg('search_it_generate_article_http_error') .' '. $scanurl . '<br>' . $response_text );
                                $return[$langID] = SEARCH_IT_ART_NOTOK;
                            }
                            continue;
                        }

                 } catch (rex_socket_exception $e) {
                    $articleText = '';
                    rex_logger::factory()->log( 'Warning', rex_i18n::msg('search_it_generate_article_socket_error') .' '.$scanurl. '<br>' .$e->getMessage() );
                    $return[$langID] = SEARCH_IT_ART_ERROR;
                    continue;

                 }


                // regex time
                preg_match_all('/<!--\ssearch_it\s([0-9]*)\s?-->(.*)<!--\s\/search_it\s(\1)\s?-->/sU', $articleText, $matches, PREG_SET_ORDER);
                $articleText = '';
                foreach ($matches as $match) {
                    if ( $match[1] == $_id || $match[1] == '' ) {
                        // eventuell in diesem enthaltene weitere tags entfernen
                        $articleText .= ' ' . preg_replace('/<!--\s\/?search_it\s[^(-->)]*\s?-->/s','', $match[2]);
                    }
                }


                $insert = rex_sql::factory();
                $articleData = [];

                $articleData['texttype'] = 'article';
                $articleData['ftable'] = $this->tablePrefix . 'article';
                $articleData['fcolumn'] = NULL;
                $articleData['clang'] = $article->getClang();
                $articleData['fid'] = intval($_id);
                $articleData['catid'] = $article->getCategoryId();
                $articleData['unchangedtext'] = $articleText;
                $plaintext = $this->getPlaintext($articleText);
                $articleData['plaintext'] = $plaintext;

                if (array_key_exists($this->tablePrefix . 'article', $this->includeColumns)) {
                    $additionalValues = [];
                    $select->flushValues();
                    $select->setTable($this->tablePrefix . 'article');
                    $select->setWhere('id = ' . $_id . ' AND clang_id = ' . $langID);
                    $select->select('`' . implode('`,`', $this->includeColumns[$this->tablePrefix . 'article']) . '`');
                    foreach ($this->includeColumns[$this->tablePrefix . 'article'] as $col) {
                        if ( $select->hasValue($col) ) { $additionalValues[$col] = $select->getValue($col); }
                    }

                    $articleData['values'] = serialize($additionalValues);
                }

                foreach (preg_split('~[[:punct:][:space:]]+~ismu', $plaintext) as $keyword) {
                    if ($this->significantCharacterCount <= mb_strlen($keyword, 'UTF-8')) {
                        $keywords[] = array('search' => $keyword, 'clang' => $langID);
                    }
                }

                $articleData['teaser'] = $this->getTeaserText($plaintext);

                $insert->setTable($this->tempTablePrefix . 'search_it_index');
                $insert->setValues($articleData);
                $insert->insert();


                $return[$langID] = SEARCH_IT_ART_GENERATED;
            }
        }

        $this->storeKeywords($keywords, false);

        return $return;
    }


    /**
     * Indexes a certain url from url Addon.
     *
     * @param int $url_hash url_generator_url table id
     * @param int $article_id redaxo article id
     * @param int $profile_id url addon profile id
     * @param int $data_id url addon profile id
     * @param int $clang_id redaxo clang id
     *
     * @return int
     */
    public function indexURL($url_hash, $article_id, $clang_id, $profile_id, $data_id)
    {
        $return = [];
        $keywords = [];

		$delete = rex_sql::factory();
		$where = "ftable = '". $this->urlAddOnTableName ."' AND fid = '". $url_hash ."' ";
		// delete from cache
		$select = rex_sql::factory();
		$select->setTable($this->tempTablePrefix . 'search_it_index');
		$select->setWhere($where);
		$select->select('id');

		$indexIds = [];
		foreach ($select->getArray() as $result) {
			$indexIds[] = $result['id'];
		}
		$this->deleteCache($indexIds);

		// delete old
		$delete->setTable($this->tempTablePrefix . 'search_it_index');
		$delete->setWhere($where);
		$delete->delete();

		// index article
		$article = rex_article::get($article_id, $clang_id);
		if ( is_null($article)) {
			$return[$clang_id] = SEARCH_IT_ART_IDNOTFOUND;
		}
		else if (is_object($article) AND ($article->isOnline() OR rex_addon::get('search_it')->getConfig('indexoffline'))) {
			try {
				$url_profile = \Url\Profile::get($profile_id);
				$server = rtrim(rex::getServer(), "/");
				$search_it_build_index = "do-it";
				if(rex_addon::get('yrewrite')->isAvailable()) {
					$hit_domain = rex_yrewrite::getDomainByArticleId($article_id, $clang_id);
					$server = rtrim($hit_domain->getUrl(), "/");
					$search_it_build_index = "do-it-with-yrewrite";
				}

				$scanurl = rex_getUrl($article_id, $clang_id, [$url_profile->getNamespace() => $data_id, 'search_it_build_index' => $search_it_build_index],'&');
				if(strpos($scanurl, 'http') === false) {
					// URL addon multidomain site return server name in url
					$scanurl = $server .'/'. ltrim(str_replace(['../', './'], '', $scanurl),"/");
				}

				$files_socket = rex_socket::factoryURL($scanurl);
				if (rex_addon::get('search_it')->getConfig('htaccess_user') != '' && rex_addon::get('search_it')->getConfig('htaccess_pass') != '') {
					$files_socket->addBasicAuthorization(rex_addon::get('search_it')->getConfig('htaccess_user'), rex_addon::get('search_it')->getConfig('htaccess_pass'));
				}
				$response = $files_socket->doGet();

				$redircount = 0;
				while ($response->isRedirection() && $redircount < 3) {

					$lastscanurl = $scanurl;
					$scanurl = str_replace(array('../', './'), '/', $response->getHeader('location'));

					if (strpos($scanurl,'//') === false ) {
						$parts = parse_url($lastscanurl);
						if ( isset($parts['scheme']) && isset($parts['host']) ) {
							$scanurl = $parts['scheme'] . '://' . $parts['host'] . $scanurl;
						}
					}
					$scanurl .= ( strpos($scanurl,'?') !== false ? '&' : '?').'search_it_build_index=redirect';
					//rex_logger::factory()->log('Warning','Redirect von '.$lastscanurl.' zu '.$scanurl.', '.$response->getHeader());

					$files_socket = rex_socket::factoryURL($scanurl);
					if (rex_addon::get('search_it')->getConfig('htaccess_user') != '' && rex_addon::get('search_it')->getConfig('htaccess_pass') != '') {
						$files_socket->addBasicAuthorization(rex_addon::get('search_it')->getConfig('htaccess_user'),rex_addon::get('search_it')->getConfig('htaccess_pass'));
					}
					$response = $files_socket->doGet();
					$redircount++;
				}
				if ($response->isOk()) {
					$articleText = $response->getBody();
				} else {
					$articleText = '';
					!is_null($response) ? $response_text = $response->getStatusCode() . ' - ' . $response->getStatusMessage() : $response_text = '';
					if ( $response->isRedirection() ) {
						$return[$clang_id] = SEARCH_IT_URL_REDIRECT;
						$response_text = rex_i18n::msg('search_it_generate_article_redirect');
						rex_logger::factory()->log( 'Warning', rex_i18n::msg('search_it_generate_article_http_error') .' '. $scanurl . '<br>' . $response_text );
					} else if ( $response->getStatusCode() == '404' ) {
						$return[$clang_id] = SEARCH_IT_URL_404;
						rex_logger::factory()->log( 'Warning', rex_i18n::msg('search_it_generate_article_404_error') .' '. $scanurl . '<br>' . $response_text );
					} else {
						rex_logger::factory()->log( 'Warning', rex_i18n::msg('search_it_generate_article_http_error') .' '. $scanurl . '<br>' . $response_text );
						$return[$clang_id] = SEARCH_IT_URL_NOTOK;
					}
					return $return;
				}

			} catch (rex_socket_exception $e) {
				$articleText = '';
				rex_logger::factory()->log( 'Warning', rex_i18n::msg('search_it_generate_article_socket_error') .' '.$scanurl. '<br>' .$e->getMessage() );
				$return[$clang_id] = SEARCH_IT_URL_ERROR;
			}
			// regex time
			preg_match_all('/<!--\ssearch_it\s([0-9]*)\s?-->(.*)<!--\s\/search_it\s(\1)\s?-->/sU', $articleText, $matches, PREG_SET_ORDER);
			$articleText = '';
			foreach ($matches as $match) {
				if ( $match[1] == $article_id || $match[1] == '' ) {
					// eventuell in diesem enthaltene weitere tags entfernen
					$articleText .= ' ' . preg_replace('/<!--\s\/?search_it\s[^(-->)]*\s?-->/s','', $match[2]);
				}
			}

			$insert = rex_sql::factory();
			$articleData = [];

			$articleData['texttype'] = 'url';
			$articleData['ftable'] = $this->urlAddOnTableName;
			$articleData['fcolumn'] = NULL;
			$articleData['clang'] = $clang_id;
			$articleData['fid'] = $url_hash;
			$articleData['catid'] = $article->getCategoryId();
			$articleData['unchangedtext'] = $articleText;
			$plaintext = $this->getPlaintext($articleText);
			$articleData['plaintext'] = $plaintext;

			if (array_key_exists($this->urlAddOnTableName, $this->includeColumns)) {
				$additionalValues = [];
				$select->flushValues();
				$select->setTable($this->urlAddOnTableName);
				$select->setWhere('url_hash = "' . $url_hash . '"');
				$select->select('`' . implode('`,`', $this->includeColumns[$this->urlAddOnTableName]) . '`');
				foreach ($this->includeColumns[$this->urlAddOnTableName] as $col) {
					if ( $select->hasValue($col) ) { $additionalValues[$col] = $select->getValue($col); }
				}

				$articleData['values'] = serialize($additionalValues);
			}

			foreach (preg_split('~[[:punct:][:space:]]+~ismu', $plaintext) as $keyword) {
				if ($this->significantCharacterCount <= mb_strlen($keyword, 'UTF-8')) {
					$keywords[] = array('search' => $keyword, 'clang' => $clang_id);
				}
			}

			$articleData['teaser'] = $this->getTeaserText($plaintext);
			$insert->setTable($this->tempTablePrefix . 'search_it_index');
			$insert->setValues($articleData);
			$insert->insert();


			$return[$clang_id] = SEARCH_IT_URL_GENERATED;

			$this->storeKeywords($keywords, false);
		}

        return $return;
    }

	/**
     * Excludes an article from the index.
     *
     * @param int $_id
     * @param mixed $_clang
     */
    public function unindexArticle($_id, $_clang = false)
    {
        // exclude article
        $art_sql = rex_sql::factory();
        $art_sql->setTable($this->tempTablePrefix . 'search_it_index');

        $where = "fid = " . intval($_id) . " AND texttype='article'";
        if ($_clang !== false) {
            $where .= " AND clang='" . intval($_clang) . "'";
        }

        $art_sql->setWhere($where);
        $art_sql->delete();

        // delete from cache
        $select = rex_sql::factory();
        $select->setTable($this->tempTablePrefix. 'search_it_index');
        $select->setWhere($where);
        $select->select('id');

        $indexIds = [];
        foreach ($select->getArray() as $result) {
            $indexIds[] = $result['id'];
        }
        $this->deleteCache($indexIds);
    }

	/**
     * Excludes an url from the index.
     *
     * @param int $url_hash
     */
    public function unindexURL($url_hash)
    {
        // exclude url
        $art_sql = rex_sql::factory();
        $art_sql->setTable($this->tempTablePrefix . 'search_it_index');

        $where = "fid = '" . $url_hash . "' AND texttype='url'";
        $art_sql->setWhere($where);
        $art_sql->delete();

        // delete from cache
        $select = rex_sql::factory();
        $select->setTable($this->tempTablePrefix. 'search_it_index');
        $select->setWhere($where);
        $select->select('id');

        $indexIds = [];
        foreach ($select->getArray() as $result) {
            $indexIds[] = $result['id'];
        }
        $this->deleteCache($indexIds);
    }

    /**
     * Indexes a certain column.
     * Returns the number of the indexed rows or false.
     *
     * @param string $_table
     * @param mixed $_column
     * @param mixed $_idcol
     * @param mixed $_id
     * @param mixed $_start
     * @param mixed $_count
     *
     * @return mixed
     */
    public function indexColumn($_table, $_column, $_idcol = false, $_id = false, $_start = false, $_count = false, $_wherecondition = false)
    {
        $sqltest = rex_sql_table::get($_table);
        if ( !$sqltest->exists() ) {
            rex_logger::factory()->log( 'Warning', rex_i18n::rawMsg('search_it_generate_table_not_exists', $_table));
            return false;
        } else {
            if (!$sqltest->hasColumn($_column) ) {
                rex_logger::factory()->log( 'Warning', rex_i18n::rawMsg('search_it_generate_col_not_exists', $_column, $_table));
                return false;
            }
        }
        $delete = rex_sql::factory();
        $delete->setTable($this->tempTablePrefix . 'search_it_index');

        $where = sprintf(" `ftable` = '%s' AND `fcolumn` = '%s' AND `texttype` = 'db_column'", $_table, $_column);
        if (is_string($_idcol) AND ($_id !== false)) {
            $where .= sprintf(' AND fid = %d', $_id);
        }
        $delete->setWhere($where);

        $cache = clone $delete;

        // delete from cache
        $indexIds = [];
        if ($cache->select('id')) {
            foreach ($cache->getArray() as $result) {
                $indexIds[] = $result['id'];
            }
            $this->deleteCache($indexIds);
        }

        // delete from index
        if ( $_start === false || $_start == 0 ) { $delete->delete(); }


        // NEW
        $sql = rex_sql::factory();

        // get primary key column(s)
        $primaryKeys = [];
        foreach ($sql->getArray("SHOW COLUMNS FROM `" . $_table . "` WHERE `KEY` = 'PRI'") as $col) {
            $primaryKeys[] = $col['Field'];
        }

        // index column
        $sql->flushValues();
        $sql->setTable($_table);

        $where = '1 ';
        if (is_string($_idcol) AND $_id) {
            $where .= sprintf(' AND (%s = %d)', $_idcol, $_id);
        } elseif (is_numeric($_start) AND is_numeric($_count)) {
            $where .= ' LIMIT ' . $_start . ',' . $_count;
        }
        $sql->setWhere($where);

        $count = false;
        if ($sql->select('*')) {

            $count = 0;
            $keywords = [];

            foreach ($sql->getArray() as $row) {
                if (!empty($row[$_column]) AND ( rex_addon::get('search_it')->getConfig('indexoffline') OR $this->tablePrefix . 'article' != $_table OR $row['status'] == '1')
                    AND ($this->tablePrefix . 'article' != $_table OR !in_array($row['id'], $this->excludeIDs))
                ) {
                    $insert = rex_sql::factory();
                    $indexData = [];

                    $indexData['texttype'] = 'db_column';
                    $indexData['ftable'] = $_table;
                    $indexData['fcolumn'] = $_column;

                    if (array_key_exists('clang', $row)) {
                        $indexData['clang'] = $row['clang_id'];
                    } else {
                        $indexData['clang'] = NULL;
                    }
                    $indexData['fid'] = NULL;
                    if (is_string($_idcol) AND array_key_exists($_idcol, $row)) {
                        $indexData['fid'] = $row[$_idcol];
                    } elseif ($_table == $this->tablePrefix . 'article') {
                        $indexData['fid'] = $row['id'];
                    } elseif (count($primaryKeys) == 1) {
                        $indexData['fid'] = $row[$primaryKeys[0]];
                    } elseif (count($primaryKeys)) {
                        $fids = [];
                        foreach ($primaryKeys as $pk) {
                            $fids[$pk] = $row[$pk];
                        }
                        $indexData['fid'] = json_encode($fids);
                    }

                    if (is_null($indexData['fid'])) {
                        // keine id Spalte und keine primär schlüssel auch die views landen hier
                        $indexData['fid'] = $this->getMaxFID($_table);
                    }
                    if (array_key_exists('parent_id', $row)) {
                        $indexData['catid'] = $row['parent_id'];
                        if ($_table == $this->tablePrefix . 'article') {
                            $indexData['catid'] = intval($row['startarticle']) ? $row['id'] : $row['parent_id'];
                        }
                    } elseif (array_key_exists('category_id', $row)) {
                        $indexData['catid'] = $row['category_id'];
                    } else {
                        $indexData['catid'] = NULL;
                    }
                    $additionalValues = [];
                    foreach ($this->includeColumns[$_table] as $col) {
                        if ( isset($row[$col]) ) { $additionalValues[$col] = $row[$col]; }
                    }
                    $indexData['values'] = serialize($additionalValues);

                    $indexData['unchangedtext'] = (string)$row[$_column];
                    $plaintext = $this->getPlaintext($row[$_column]);
                    $indexData['plaintext'] = $plaintext;

                    foreach (preg_split('~[[:punct:][:space:]]+~ismu', $plaintext) as $keyword) {
                        if ($this->significantCharacterCount <= mb_strlen($keyword, 'UTF-8')) {
                            $keywords[] = array('search' => $keyword, 'clang' => is_null($indexData['clang']) ? false : $indexData['clang']);
                        }
                    }

                    $indexData['teaser'] = '';
                    if ($this->tablePrefix . 'article' == $_table) {
                        $rex_article = new rex_article_content(intval($row['id']), intval($row['clang_id']));
                        $teaser = true;
                        $article_content_file = rex_path::addonCache('structure', intval($row['id']) . '.' . intval($row['clang_id']) . '.content');
                        if (!file_exists($article_content_file)) {
                            $generated = rex_content_service::generateArticleContent(intval($row['id']), intval($row['clang_id']));
                            if ($generated !== true) {
                                $teaser = false;
                                continue;
                            }
                        }

                        if (file_exists($article_content_file) AND preg_match('~(header\s*\(\s*["\']\s*Location\s*:)|(rex_redirect\s*\()~isu', rex_file::get($article_content_file))) {
                            $teaser = false;
                        }

                        $indexData['teaser'] = $teaser ? $this->getTeaserText($this->getPlaintext($rex_article->getArticle())) : '';
                    }

                    $insert->setTable($this->tempTablePrefix . 'search_it_index');
                    $insert->setValues($indexData);
                    $insert->insert();

                    $count++;
                }
            }

            $this->storeKeywords($keywords, false);

        }

        return $count;
    }

    /**
     * Indexes a certain file.
     * Returns SEARCH_IT_FILE_GENERATED or an error code.
     *
     * @param string $_filename
     * @param mixed $_clang
     * @param mixed $_doPlaintext
     * @param mixed $_articleData
     *
     * @return mixed
     */
    public function indexFile($_filename, $_doPlaintext = false, $_clang = false, $_fid = false, $_catid = false)
    {
        // $_filename comes with path but stripped of first slash
        // extract file-extension
        $filenameArray = explode('.', $_filename);
        $fileext = $filenameArray[count($filenameArray) - 1];

        // check file-extension
        if ((!in_array($fileext, $this->fileExtensions) AND !empty($this->fileExtensions)) ) {
            return SEARCH_IT_FILE_FORBIDDEN_EXTENSION;
        }

        // delete cache
        $delete = rex_sql::factory();

        $where = sprintf(" `filename` = %s AND `texttype` = 'file'", $delete->escape($_filename));
        if (is_int($_clang)) {
            $where .= sprintf(' AND clang = %d', $_clang);
        }
        if (is_int($_fid)) {
            $where .= sprintf(' AND fid = %d', $_fid);
        } elseif (is_array($_fid)) {
            $where .= sprintf(" AND fid = %s", $delete->escape(json_encode($_fid)));
        }
        if (is_int($_catid)) {
            $where .= sprintf(' AND catid = %d', $_catid);
        }

        // delete from cache
        $select = rex_sql::factory();
        $select->setTable($this->tempTablePrefix . 'search_it_index');
        $select->setWhere($where);
        $indexIds = [];
        if ($select->select('id')) {
            foreach ($select->getArray() as $result) {
                $indexIds[] = $result['id'];
            }
            $this->deleteCache($indexIds);
        }

        // delete old data
        $delete->setTable($this->tempTablePrefix . 'search_it_index');
        $delete->setWhere($where);
        $delete->delete();

        // index file
        $text = '';
        $plaintext = '';

        switch ($fileext) {
            // pdf-files
            case 'pdf':

                // try XPDF
                $return = 0;
                $xpdf = false;
                $error = false;

                if (function_exists('exec')) {
                    $tempFile = tempnam(rex_path::cache() . 'addons/mediapool/', 'search_it');
                    $encoding = 'UTF-8';
                    //echo 'pdftotext ' . escapeshellarg(rex_path::base($_filename)) . ' ' . escapeshellarg($tempFile) . ' -enc ' . $encoding;
                    exec('pdftotext  -enc ' . $encoding.' '. escapeshellarg(rex_path::base($_filename)) . ' ' . escapeshellarg($tempFile) , $dummy, $return);

                    if ($return > 0) {
                        if ($return == 1) {
                            $error = SEARCH_IT_FILE_XPDFERR_OPENSRC;
                        }
                        if ($return == 2) {
                            $error = SEARCH_IT_FILE_XPDFERR_OPENDEST;
                        }
                        if ($return == 3) {
                            $error = SEARCH_IT_FILE_XPDFERR_PERM;
                        }
                        if ($return == 99) {
                            $error = SEARCH_IT_FILE_XPDFERR_OTHER;
                        }
                    } else {
                        if (false === $text = @file_get_contents($tempFile)) {
                            $error = SEARCH_IT_FILE_NOEXIST;
                        } else {
                            $xpdf = true;
                        }
                    }

                    unlink($tempFile);
                }

                if (!$xpdf) {
                    // if xpdf returned an error, try pdf2txt via php
                    if (false === $pdfContent = @rex_file::get(rex_path::base($_filename))) {
                        $error = SEARCH_IT_FILE_NOEXIST;
                    } else {
                        $text = pdf2txt::directConvert($pdfContent);
                        $error = false;
                    }
                }

                if ($error !== false) {
                    return $error;
                } elseif (trim($text) == '') {
                    return SEARCH_IT_FILE_EMPTY;
                }

                $plaintext = $this->getPlaintext($text);
                break;

            // html- or php-file
            case 'htm':
            case 'html':
            case 'php':
                if (false === $text = @rex_file::get(rex_path::base($_filename))) {
                    return SEARCH_IT_FILE_NOEXIST;
                }

                $plaintext = $this->getPlaintext($text);
                break;

            // other filetype
            default:
                if (false === $text = @rex_file::get(rex_path::base($_filename))) {
                    return SEARCH_IT_FILE_NOEXIST;
                }

        }

        $text = @iconv(mb_detect_encoding($text), 'UTF-8', $text);

        // Plaintext
        if (empty($plaintext)) {
            if ($_doPlaintext) {
                $plaintext = $this->getPlaintext($text);
            } else {
                $plaintext = $text;
            }
        }

        // index file-content
        $insert = rex_sql::factory();

        $fileData['texttype'] = 'file';
        if ($_fid !== false) {
            $fileData['ftable'] = $this->tablePrefix . 'media';
        }
        $fileData['filename'] = $_filename;
        $fileData['fileext'] = $fileext;
        if ($_clang !== false) {
            $fileData['clang'] = intval($_clang);
        }
        if ($_fid !== false) {
            $fileData['fid'] = intval($_fid);
        } else {
            $fileData['fid'] = NULL;
        }

        if (is_null($fileData['fid'])) {
            $fileData['fid'] = $this->getMinFID();
        }

        if ($_catid !== false) {
            $fileData['catid'] = intval($_catid);
        }
        $fileData['unchangedtext'] = $text;
        $fileData['plaintext'] = $plaintext;

        $keywords = [];
        foreach (preg_split('~[[:punct:][:space:]]+~ismu', $plaintext) as $keyword) {
            if ($this->significantCharacterCount <= mb_strlen($keyword, 'UTF-8')) {
                $keywords[] = array('search' => $keyword, 'clang' => !isset($fileData['clang']) ? false : $fileData['clang']);
            }
        }
        $this->storeKeywords($keywords, false);

        $fileData['teaser'] = $this->getTeaserText($plaintext);

        $insert->setTable($this->tempTablePrefix . 'search_it_index');
        $insert->setValues($fileData);
        $insert->insert();

        return SEARCH_IT_FILE_GENERATED;
    }

    /**
     * Strips the HTML-Tags from a text and replaces them with spaces or line breaks
     *
     * @param string $_text
     *
     * @return string
     */
    private function getPlaintext($_text)
    {
        $process = true;
        $extensionReturn = rex_extension::registerPoint(new rex_extension_point('SEARCH_IT_PLAINTEXT', $_text));
        if (is_array($extensionReturn)) {
            $_text = $extensionReturn['text'];
            $process = !empty($extensionReturn['process']);
        } elseif (is_string($extensionReturn)) {
            $_text = $extensionReturn;
        }

        if ($process) {
            $tags2nl = '~</?(address|blockquote|center|del|dir|div|dl|fieldset|form|h1|h2|h3|h4|h5|h6|hr|ins|isindex|menu|noframes|noscript|ol|p|pre|table|ul)[^>]+>~siu';
            $_text = trim(strip_tags(preg_replace(array('~<(head|script).+?</(head|script)>~siu', $tags2nl, '~<[^>]+>~siu', '~[\n\r]+~siu', '~[\t ]+~siu'), array('', "\n", ' ', "\n", ' '), $_text)));
            $_text = html_entity_decode($_text,ENT_QUOTES,'UTF-8');
        }

        return preg_replace('~(\s+\n){2,}~', "\r\n", $_text);
    }

    /**
     * Gets the teaser of a text.
     *
     * @param string $_text
     *
     * @return string
     */
    private function getTeaserText($_text)
    {
        $i = 0;
        $textArray = preg_split('~\s+~siu', $_text, $this->maxTeaserChars);

        $return = '';
        $aborted = false;
        foreach ($textArray as $word) {
            if ((($strlen = strlen($word)) + $i) > $this->maxTeaserChars) {
                $aborted = true;
                break;
            }

            $return .= $word . ' ';
            $i += $strlen + 1;
        }

        if ($aborted) {
            $return .= $this->ellipsis;
        }

        return $return;
    }

    /**
     * In some cases there is no id for the field fid in the index table (like media files). Therefore Search it counts into the negative.
     *
     */
    private static function getMinFID()
    {
        $minfid_sql = rex_sql::factory();
        $minfid_result = $minfid_sql->getArray('SELECT MIN(CONVERT(fid, SIGNED)) as minfid FROM `' . self::tempTablePrefix.'search_it_index'. '`');
        $minfid = intval($minfid_result[0]['minfid']);

        return ($minfid < 0) ? --$minfid : -1;
    }

    private static function getMaxFID($_table)
    {
        $maxfid_sql = rex_sql::factory();
        $maxfid_result = $maxfid_sql->getArray('SELECT MAX(CONVERT(fid, SIGNED)) as maxfid FROM `' . self::tempTablePrefix.'search_it_index' . '` WHERE ftable = "'.$_table.'" ');
        $maxfid = intval($maxfid_result[0]['maxfid']);

        return ($maxfid > 0) ? ++$maxfid : 1;
    }

	/**
     * Deletes the complete search index.
     *
     */
    public function deleteIndex()
    {
        $delete = rex_sql::factory();
		$delete->setQuery('TRUNCATE '. $this->tempTablePrefix .'search_it_index');

        $this->deleteCache();
    }

	/**
     * Deletes the search index for given type
     * @param string $texttype Index text type
     */
    public function deleteIndexForType($texttype)
    {
        $sql = rex_sql::factory();
		$query = 'SELECT id FROM '. $this->tempTablePrefix .'search_it_index WHERE texttype = "'. $texttype .'";';
		$deleteIds = [];
        foreach ($sql->getArray($query) as $cacheId) {
			$deleteIds[] = $cacheId['id'];
		}
		// Delete index
		$sql->setQuery('DELETE FROM '. $this->tempTablePrefix .'search_it_index WHERE texttype = "'. $texttype .'"');
		// Delete cache
		$this->deleteCache($deleteIds);
    }

    /**
     * Exclude Articles with the transfered IDs.
     *
     * Expects an array with the IDs as parameters.
     */
    private function setExcludeIDs($_ids)
    {
        foreach ($_ids as $key => $id) {
            $this->excludeIDs[] = intval($id);
        }

        $this->excludeIDs = array_unique($this->excludeIDs);
    }

    /**
     * Sets words, which must not be found.
     *
     * Expects an array with the words as parameters.
     */
    public function setBlacklist($_words)
    {
        foreach ($_words as $key => $word) {
            $this->blacklist[] = $tmpkey = (string)($this->ci ? strtolower($word) : $word);
            $this->hashMe .= $tmpkey;
        }
    }




    /* search */
    /**
     * Sets search string.
     *
     * Expects a string.
     */
    public function setSearchString($_searchString)
    {
        $this->searchString = $_searchString;
    }

    /**
     * Converts the search string to an array.
     *
     * Returns the number of search terms.
     *
     * @param string $_searchString
     *
     * @return int
     */
    public function parseSearchString($_searchString)
    {
        // reset searchArray
        $this->searchArray = [];

        $matches = [];
        preg_match_all('~(?:(\+*)"([^"]*)")|(?:(\+*)(\S+))~isu', $_searchString, $matches, PREG_SET_ORDER);

        $count = 0;
        $searchWords = [];

        foreach ($matches as $match) {
            if (count($match) == 5) {
                // words without double quotes (foo)
                $word = $match[4];
                $plus = $match[3];
            } elseif (!empty($match[2])) {
                // words with double quotes ("foo bar")
                $word = $match[2];
                $plus = $match[1];
            } else {
                continue;
            }
            if ( in_array($word,$searchWords) ) { continue; } else { $searchWords[] = $word; }

            $notBlacklisted = true;
            // blacklisted words are excluded
            foreach ($this->blacklist as $blacklistedWord) {
                if (preg_match('~\b' . preg_quote($blacklistedWord, '~') . '\b~isu', $word)) {
                    $this->blacklisted[] = array($blacklistedWord => $word);
                    $notBlacklisted = false;
                }
            }

            if ($notBlacklisted) {
                // whitelisted words get extra weighted
                $this->searchArray[$count] = array('search' => $word,
                    'weight' => strlen($plus) + 1 + (array_key_exists($word, $this->whitelist) ? $this->whitelist[$word] : 0),
                    'clang' => $this->clang
                );
                $count++;
            }
        }

        return $count;
    }

    /**
     * Which words are important?
     *
     * This method adds weight to special words.
     * If an word already exists, the method adds the weight.
     * Expects an array with the keys containing the words
     * and the values containing the weight to add.
     *
     * @param array $_whitelist
     *
     *
     */
    public function addWhitelist($_whitelist)
    {
        foreach ($_whitelist as $word => $weight) {
            $key = (string)($this->ci ? strtolower($word) : $word);
            $this->hashMe .= $key;
            $this->whitelist[$key] = intval($this->whitelist[$key]) + intval($weight);
        }
    }

    /**
     * Sets array of IDs for search restriction
     */
    private function setSearchInIDs($_searchInIDs, $_reset = false)
    {
        if ($_reset) {
            $this->searchInIDs = [];
        }
        if (array_key_exists('articles', $_searchInIDs)) {
            if (!array_key_exists('articles', $this->searchInIDs)) {
                $this->searchInIDs['articles'] = [];
            }
            foreach ($_searchInIDs['articles'] as $id) {
                if ($id = intval($id)) {
                    $this->searchInIDs['articles'][] = $id;
                    $this->hashMe .= 'a' . $id;
                }
            }
        }

        if (array_key_exists('categories', $_searchInIDs)) {
            if (!array_key_exists('categories', $this->searchInIDs)) {
                $this->searchInIDs['categories'] = [];
            }
            foreach ($_searchInIDs['categories'] as $id) {
                if ($id = intval($id)) {
                    $this->searchInIDs['categories'][] = $id;
                    $this->hashMe .= 'c' . $id;
                }
            }
        }

        if (array_key_exists('filecategories', $_searchInIDs)) {
            if (!array_key_exists('filecategories', $this->searchInIDs)) {
                $this->searchInIDs['filecategories'] = [];
            }
            foreach ($_searchInIDs['filecategories'] as $id) {
                if ($id = intval($id)) {
                    $this->searchInIDs['filecategories'][] = $id;
                    $this->hashMe .= 'f' . $id;
                }
            }
        }

        if (array_key_exists('db_columns', $_searchInIDs)) {
            if (!array_key_exists('db_columns', $this->searchInIDs)) {
                $this->searchInIDs['db_columns'] = [];
            }
            foreach ($_searchInIDs['db_columns'] as $table => $columnArray) {
                $this->hashMe .= $table;
                $tmp = [];
                foreach ($columnArray as $column) {
                    $tmp[] = $column;
                    $this->hashMe .= $column;
                }
                if (!array_key_exists($table, $this->searchInIDs['db_columns'])) {
                    $this->searchInIDs['db_columns'][$table] = $tmp;
                } else {
                    $this->searchInIDs['db_columns'][$table] = array_merge($this->searchInIDs['db_columns'][$table], $tmp);
                }
            }
        }
    }

    /**
     * if IDs are set to filter search, you can tell Search it to search any article anyway
     *
     * @param bool $_bool
     *
     * @ignore
     */
    public function setSearchAllArticlesAnyway($_bool = false)
    {
        $this->searchAllArticlesAnyway = $_bool;
        $this->hashMe .= $_bool;
    }

    /**
     * deprecated: use setSearchAllArticlesAnyway()
     *
     * @param bool $_bool
     *
     * @ignore
     */
    public function doSearchArticles($_bool = false)
    {
        $this->searchAllArticlesAnyway = $_bool;
        $this->hashMe .= $_bool;
    }

    /**
     * Sets the IDs of the articles which are only to be searched through.
     *
     * Expects an array with the IDs as parameters.
     */
    public function searchInArticles($_ids)
    {
        $this->setSearchInIDs(array('articles' => $_ids));
    }

    /**
     * Sets the IDs of the categories which are only to be searched through.
     *
     * Expects an array with the IDs as parameters.
     */
    public function searchInCategories($_ids)
    {
        $this->setSearchInIDs(array('categories' => $_ids));
    }

    /**
     * Sets an ID of a category as root search category. All searched articles need to be contained by it.
     *
     * Expects an ID as parameter.
     */
    public function searchInCategoryTree($_id)
    {
        $subcats = self::getChildList($_id);
        $this->setSearchInIDs(array('categories' => $subcats));
    }

    /**
     * Retrieves a list of IDs of all categories which are subcategories to the given one.
     *
     * Expects a category ID as parameter.
     */
    private function getChildList($_id)
    {
        $subs= rex_category::get($_id)->getChildren();
        $childlist = [];
        $childlist[] = (int) $_id;
        if( !empty($subs)){
            foreach ( $subs as $sub){
                $childlist = array_merge($childlist,self::getChildList((int)$sub->getId()));
            }
        }
        return $childlist;
    }

    /**
     * Sets the IDs of the mediapool-categories which are only to be searched through.
     *
     * Expects an array with the IDs as parameters.
     */
    public function searchInFileCategories($_ids)
    {
        $this->setSearchInIDs(array('filecategories' => $_ids));
    }

    /**
     * Sets the columns which only should be searched through.
     *
     * @param string $_table
     * @param string $_column
     */
    public function searchInDbColumn($_table, $_column)
    {
        $this->setSearchinIDs(array('db_columns' => array($_table => array($_column))));
    }

    /**
     * Sets an addition WHERE Condition to the search query.
     *
     * Expects a string suitable as SQL WHERE condition.
     */
    public function setWhere($_where)
    {
        $this->where = $_where;
        $this->hashMe .= $_where;
    }

    /**
     * Sets the sort order of the results.
     *
     * The parameter has to be an array with the columns as keys
     * and the direction (DESC or ASC) as value (e.g.: array['COLUMN'] = 'ASC').
     *
     * @param array $_order
	 * @param bool $put_first put new order criteria(s) first in order clause
     *
     * @return bool
     */
    public function setOrder($_order, $put_first = false)
    {
        if (!is_array($_order)) {
            $this->errormessages = 'Wrong parameter. Expecting an array';
            return false;
        }
        if ($put_first) {
            $_order = array_reverse($_order, TRUE);
        }
        $i = 0;
        $dir2upper = '';
        $col2upper = '';
        foreach ($_order as $col => $dir) {
            $i++;
            if ('RELEVANCE_SEARCH_IT' == ($col2upper = strtoupper((string)$col))) {
                $this->errormessages = sprintf('Column %d must not be named "RELEVANCE_SEARCH_IT". Column %d is ignored for the sort order', $i, $i);
            } else {
                if (!in_array($dir2upper = strtoupper((string)$dir), array('ASC', 'DESC'))) {
                    $this->errormessages = sprintf('Column %d has no correct sort order (ASC or DESC). Descending (DESC) sort order is assumed', $i);
                    $dir2upper = 'DESC';
                }
				$this->order = $put_first ? array_merge([$col => $dir2upper], $this->order) : array_merge($this->order, [$col => $dir2upper]);
                $this->hashMe .= $col2upper . $dir2upper;
            }
        }
        return true;
    }

    /**
     * group results by ftable,fid,clang ?
     *
     * @param bool $_bool
     *
     * @ignore
     */
    public function doGroupBy($_bool = true)
    {
        $this->groupBy = $_bool;
        $this->hashMe .= $_bool;
    }

    /**
     * Case sensitive or case insensitive?
     *
     * @param bool $_ci
     *
     * @ignore
     */
    public function setCaseInsensitive($_ci = true)
    {
        $this->ci = (bool)$_ci;
    }

    /**
     * Sets the language-Id.
     *
     * @param mixed $_clang
     *
     *
     */
    private function setClang($_clang)
    {
        if ($_clang === false) {
            $this->clang = false;
        } else {
            $this->clang = intval($_clang);
        }

        $this->hashMe .= $_clang;
    }



    /**
     * Sets the mode of how the keywords are logical connected.
     *
     * Are the keywords to be connected conjunctional or disjunctional?
     * Has each single keyword to be found or is one single keyword sufficient?
     *
     * @param string $_logicalMode
     *
     * @return bool
     */
    public function setLogicalMode($_logicalMode)
    {
        switch (strtolower($_logicalMode)) {
            case 'and':
            case 'konj':
            case 'strict':
            case 'sharp':
                $this->logicalMode = ' AND ';
                break;

            case 'or':
            case 'disj':
            case 'simple':
            case 'fuzzy':
                $this->logicalMode = ' OR ';
                break;

            default:
                $this->logicalMode = ' AND ';
                return false;
        }

        $this->hashMe .= $this->logicalMode;

        return true;
    }

    /**
     * Sets the mode concerning which text is to be searched through.
     *
     * You can choose between the original text, the plain text or both texts.
     *
     * @param string $_textMode
     *
     * @return bool
     */
    public function setTextMode($_textMode)
    {
        switch (strtolower($_textMode)) {
            case 'html':
            case 'xhtml':
            case 'unmodified':
            case 'original':
                $this->textMode = 'unmodified';
                break;

            case 'text':
            case 'plain':
            case 'stripped':
            case 'bare':
            case 'simple':
                $this->textMode = 'plain';
                break;

            case 'both':
            case 'all':
                $this->textMode = 'both';
                break;

            default:
                return false;
        }

        $this->hashMe .= $this->textMode;

        return true;
    }

    /**
     * Sets the MySQL search mode.
     *
     * You can choose between like and match
     *
     * @param string $_searchMode
     *
     * @return bool
     */
    public function setSearchMode($_searchMode)
    {
        switch (strtolower($_searchMode)) {
            case 'like':
            case 'match':
                $this->searchMode = strtolower($_searchMode);
                break;

            default:
                return false;
        }

        $this->hashMe .= $this->searchMode;

        return true;
    }





    /* search output */
    /**
     * Sets the surround-tags for found keywords.
     *
     * Expects either the start- and the end-tag
     * or an array with both tags.
     */
    public function setSurroundTags($_tags, $_endtag = false)
    {
        if (is_array($_tags) AND $_endtag === false) {
            $this->surroundTags = $_tags;
        } else {
            $this->surroundTags = array((string)$_tags, (string)$_endtag);
        }
        $this->hashMe .= $this->surroundTags[0] . $this->surroundTags[1];
    }

    /**
     * Sets the maximum count of results.
     *
     * Expects either the start- and the count-limit
     * or an array with both limits
     * or only the count-limit.
     *
     * example method calls:
     * setLimit(10,10);       // start with 10th result
     * setLimit(20);          // maximum of 20 results starting with the first result
     * setLimit(array(0,20)); // maximum of 20 results starting with the first result
     */
    public function setLimit($_limit, $_countLimit = false)
    {
        if (is_array($_limit) AND $_countLimit === false) {
            $this->limit = array((int)$_limit[0], (int)$_limit[1]);
        } elseif ($_countLimit === false) {

            $this->limit = array(0, (int)$_limit);
        } else {
            $this->limit = array((int)$_limit, (int)$_countLimit);
        }
        if ( empty($this->limit[1]) || !is_numeric($this->limit[1]) ) { $this->limit[1] = 10; }
        $this->hashMe .= $this->limit[0] . $this->limit[1];
    }

    /**
     * Sets the maximum count of letters the teaser of a searched through text may have.
     *
     * @param int $_count
     */
    public function setMaxTeaserChars($_count)
    {
        if ( empty($_count) || !is_numeric($_count) ) { $_count = 200; }
        $this->maxTeaserChars = intval($_count);
        $this->hashMe .= $_count;
    }

    /**
     * Sets the maximum count of letters around an found search term in the highlighted text.
     * @param int $_count
     */
    public function setMaxHighlightedTextChars($_count)
    {
        if ( empty($_count) || !is_numeric($_count) ) { $_count = 100; }
        $this->maxHighlightedTextChars = intval($_count);
        $this->hashMe .= $_count;
    }

    /**
     * Sets the type of the text with the highlighted keywords.
     *
     * @param string $_type
     *
     * @return bool
     */
    public function setHighlightType($_type)
    {
        switch ($_type) {
            case 'sentence':
            case 'paragraph':
            case 'surroundtext':
            case 'surroundtextsingle':
            case 'teaser':
            case 'array':
                $this->highlightType = $_type;
                return true;
                break;

            default:
                $this->highlightType = 'surroundtextsingle';
                return false;
        }

        $this->hashMe .= $this->highlightType;
    }

    /**
     * According to the highlight-type this method will return a string or an array.
     * Found keywords will be highlighted with the surround-tags.
     *
     * @param string $_text
     *
     * @return mixed
     */
    public function getHighlightedText($_text)
    {
        $tmp_searchArray = $this->searchArray;

        if ($this->searchHtmlEntities) {
            foreach ($this->searchArray as $keyword) {
                $this->searchArray[] = array('search' => htmlentities($keyword['search'], ENT_COMPAT, 'UTF-8'));
            }
        }

        switch ($this->highlightType) {
            case 'sentence':
            case 'paragraph':
                // split text at punctuation marks
                if ($this->highlightType == 'sentence') {
                    $regex = '~(\!|\.|\?|[\n]+)~siu';
                }
                // split text at line breaks
                if ($this->highlightType == 'paragraph') {
                    $regex = '~([\r\n])~siu';
                }

                $Apieces = preg_split($regex, $_text, -1, PREG_SPLIT_DELIM_CAPTURE);

                $search = [];
                $replace = [];
                foreach ($this->searchArray as $keyword) {
                    $search[] = preg_quote($keyword['search'], '~');
                    $replace[] = '~' . preg_quote($keyword['search'], '~') . '~isu';
                }

                $i = 0;
                for ($i = 0; $i < count($Apieces); $i++) {
                    if (preg_match('~(' . implode('|', $search) . ')~isu', $Apieces[$i])) {
                        break;
                    } elseif (preg_match('~(' . implode('|', $search) . ')~isu', str_replace(['\'','"'],'',iconv("utf-8","ascii//TRANSLIT",$Apieces[$i])))) {
                        break;
                    }
                }
                $return = '';
                if ($i < count($Apieces)) {
                    $return .= $Apieces[$i];
                }

                $cutted = [];
                preg_match('~^.*?(' . implode('|', $search) . ').{0,' . $this->maxHighlightedTextChars . '}~imsu', $return, $cutted);
                if (!empty($cutted)) {
                    $needEllipses = false;
                    if (isset($cutted[1]) && strlen($cutted[1]) != strlen($return)) {
                        $needEllipses = true;
                    }

                    $return = preg_replace($replace, $this->surroundTags[0] . '$0' . $this->surroundTags[1], substr($cutted[0], 0, strrpos($cutted[0], ' ')));

                    if ($needEllipses) {
                        $return .= ' ' . $this->ellipsis;
                    }
                }
                return $return;
                break;

            case 'surroundtext':
            case 'surroundtextsingle':
            case 'array':
                $startEllipsis = false;
                $endEllipsis = false;
                $Ahighlighted = [];
                $_text = preg_replace('~\s+~', ' ', $_text);
                $replace = [];
                foreach ($this->searchArray as $keyword) {
                    $replace[] = '~' . preg_quote($keyword['search'], '~') . '~isu';
                }

                $strlen = mb_strlen($_text);
                $positions = [];
                for ($i = 0; $i < count($this->searchArray); $i++) {
                    $hits = [];
                    $offset = 0;
                    preg_match_all('~((.{0,' . $this->maxHighlightedTextChars . '})' . preg_quote($this->searchArray[$i]['search'], '~') . '(.{0,' . $this->maxHighlightedTextChars . '}))~imsu', $_text, $hits, PREG_SET_ORDER);

                    foreach ($hits as $hit) {
                        $offset = strpos($_text, $hit[0], $offset) + 1;
                        $currentposition = ceil(intval(($offset - 1) / (2 * $this->maxHighlightedTextChars)));

                        if ($this->highlightType == 'array' AND !array_key_exists($this->searchArray[$i]['search'], $Ahighlighted)) {
                            $Ahighlighted[$this->searchArray[$i]['search']] = [];
                        }

                        if (trim($hit[1]) != '') {
                            $surroundText = $hit[1];

                            if (strlen($hit[2]) > 0 AND false !== strpos($hit[2], ' ')) {
                                $surroundText = substr($surroundText, strpos($surroundText, ' '));
                            }

                            if (strlen($hit[3]) > 0 AND false !== strpos($hit[3], ' ')) {
                                $surroundText = substr($surroundText, 0, strrpos($surroundText, ' '));
                            }

                            if ($i == 0 AND strlen($hit[2]) > 0) {
                                $startEllipsis = true;
                            }

                            if ($i == (count($this->searchArray) - 1) AND strlen($hit[3]) > 0) {
                                $endEllipsis = true;
                            }

                            if ($this->highlightType == 'array') {
                                $Ahighlighted[$this->searchArray[$i]['search']][] = preg_replace($replace, $this->surroundTags[0] . '$0' . $this->surroundTags[1], trim($surroundText));
                            } else if (!in_array($currentposition, $positions)) {
                                $Ahighlighted[] = trim($surroundText);
                            }

                            $positions[] = $currentposition;

                            if ($this->highlightType == 'surroundtextsingle') {
                                break;
                            }
                        }
                    }
                }

                if ($this->highlightType == 'array') {
                    return $Ahighlighted;
                }

                $return = implode(' ' . $this->ellipsis . ' ', $Ahighlighted);

                if ($startEllipsis) {
                    $return = $this->ellipsis . ' ' . $return;
                }

                if ($endEllipsis) {
                    $return = $return . ' ' . $this->ellipsis;
                }

                $return = preg_replace($replace, $this->surroundTags[0] . '$0' . $this->surroundTags[1], $return);

                return $return;
                break;

            case 'teaser':
                $search = [];
                foreach ($this->searchArray as $keyword) {
                    $search[] = '~' . preg_quote($keyword['search'], '~') . '~isu';
                }
                return preg_replace($search, $this->surroundTags[0] . '$0' . $this->surroundTags[1], $this->getTeaserText($_text));
                break;
        }

        $this->searchArray = $tmp_searchArray;
    }



    /* caching */
    /**
     * Returns if a search term is already cached.
     * The cached result will be stored in $this->cachedArray.
     *
     * @param string $_search
     *
     * @return bool
     */
    private function isCached($_search)
    {
        $sql = rex_sql::factory();
        $sql->setTable($this->tempTablePrefix . 'search_it_cache');
        $sql->setWhere(sprintf("hash = '%s'", $this->cacheHash($_search)));

        if ($sql->select('returnarray')) {
            foreach ($sql->getArray() as $value) {
                return false !== ($this->cachedArray = unserialize($value['returnarray']));
            }
        }

        return false;
    }

    /**
     * Calculates the cache hash.
     *
     * @param string $_searchString
     *
     * @return string
     */
    private function cacheHash($_searchString)
    {
        return md5($_searchString . $this->hashMe);
    }

    /**
     * Stores a search result in the cache.
     *
     * @param string $_result
     * @param array $_indexIds
     *
     * @return bool
     */
    private function cacheSearch($_result, $_indexIds)
    {
        $sql = rex_sql::factory();
        $sql->setTable($this->tempTablePrefix . 'search_it_cache');
        $sql->setValues(array(
                'hash' => $this->cacheHash($this->searchString),
                'returnarray' => $_result
            )
        );
        $sql->insert();
        $lastId = $sql->getLastId();

        $Ainsert = [];
        foreach ($_indexIds as $id) {
            $Ainsert[] = sprintf('(%d,%d)', $id, $lastId);
        }

        if (isset($Ainsert) && implode(',', $Ainsert) != '') {
            $sql2 = rex_sql::factory();

            try {
                $sqlResult = $sql2->setQuery(
                    sprintf(
                        'INSERT INTO `%s` (index_id,cache_id) VALUES
            %s;',
                        $this->tempTablePrefix . 'search_it_cacheindex_ids',
                        implode(',', $Ainsert)
                    )
                );
                $info = 'Success';
                return true;
            } catch (rex_sql_exception $e) {
                $error = $e->getMessage();
                echo rex_warning($error);
                return false;
            }

        }
    }

    /**
     * Truncates the cache or deletes all data that are concerned with the given index-ids.
     *
     * @param mixed $_indexIds
     *
     *
     */
    public function deleteCache($_indexIds = false)
    {
        if ($_indexIds === false) {
            // delete entire search-chache
            $delete = rex_sql::factory();
            $delete->setQuery('TRUNCATE '. $this->tempTablePrefix . 'search_it_cacheindex_ids');
            $delete->setQuery('TRUNCATE '. $this->tempTablePrefix . 'search_it_cache');

        } elseif (is_array($_indexIds) AND !empty($_indexIds)) {
            $sql = rex_sql::factory();

            $query = sprintf('
            SELECT cache_id
            FROM %s
            WHERE index_id IN (%s)',
                $this->tempTablePrefix . 'search_it_cacheindex_ids',
                implode(',', $_indexIds)
            );

            $deleteIds = array(0);
            foreach ($sql->getArray($query) as $cacheId) {
                $deleteIds[] = $cacheId['cache_id'];
            }

            // delete from search-cache where indexed IDs exist
            $delete = rex_sql::factory();
            $delete->setTable($this->tempTablePrefix . 'search_it_cache');
            $delete->setWhere('id IN (' . implode(',', $deleteIds) . ')');
            $delete->delete();

            // delete the cache-ID and index-ID
            $delete2 = rex_sql::factory();
            $delete2->setTable($this->tempTablePrefix . 'search_it_cacheindex_ids');
            $delete2->setWhere('cache_id IN (' . implode(',', $deleteIds) . ')');
            $delete2->delete();

            // delete all cached searches which had no result (because now they maybe will have)
            $delete3 = rex_sql::factory();
            $delete3->setTable($this->tempTablePrefix . 'search_it_cache');
            $delete3->setWhere(sprintf('id NOT IN (SELECT cache_id FROM `%s`)', $this->tempTablePrefix . 'search_it_cacheindex_ids'));
            $delete3->delete();
        }
    }


    /* keywords */
    private function storeKeywords($_keywords, $_doCount = true)
    {
        // store similar words
        $simWordsSQL = rex_sql::factory();
        $simWords = [];
        foreach ($_keywords as $keyword) {
            if (!in_array(mb_strtolower($keyword['search'], 'UTF-8'), $this->blacklist) AND
                !in_array(mb_strtolower($keyword['search'], 'UTF-8'), $this->stopwords)
            ) {
                $simWords[] = sprintf(
                    "(%s, '%s', '%s', '%s', %s)",
                    $simWordsSQL->escape($keyword['search']),
                    ($this->similarwordsMode & SEARCH_IT_SIMILARWORDS_SOUNDEX) ? soundex($keyword['search']) : '',
                    ($this->similarwordsMode & SEARCH_IT_SIMILARWORDS_METAPHONE) ? metaphone($keyword['search']) : '',
                    ($this->similarwordsMode & SEARCH_IT_SIMILARWORDS_COLOGNEPHONE) ? soundex_ger($keyword['search']) : '',
                    (isset($keyword['clang']) AND $keyword['clang'] !== false) ? $keyword['clang'] : '-1'
                );
            }
        }

        if (!empty($simWords)) {
            $simWordsSQL->setQuery(
                sprintf("
              INSERT INTO `%s`
              (keyword, soundex, metaphone, colognephone, clang)
              VALUES
              %s
              ON DUPLICATE KEY UPDATE count = count + %d",
                    $this->tempTablePrefix . 'search_it_keywords',
                    implode(',', $simWords),
                    $_doCount ? 1 : 0
                )
            );
        }
    }

    public function deleteKeywords()
    {
        $kw_sql = rex_sql::factory();
        return $kw_sql->setQuery(sprintf('TRUNCATE TABLE `%s`', $this->tempTablePrefix . 'search_it_keywords'));
    }


    /**
     * Executes the search.
     *
     * @param string $_search
     *
     * @return array
     */
    function search($_search)
    {
        $startTime = microtime(true);

        $return = [];
        $return['errormessages'] = '';
        $return['simwordsnewsearch'] = '';
        $return['simwords'] = [];

        $this->searchString = trim(stripslashes($_search));
        $keywordCount = $this->parseSearchString($this->searchString); // setzt $this->searchArray

        if (empty($this->searchString) OR empty($this->searchArray)) {
            return array(
                'count' => 0,
                'hits' => [],
                'keywords' => [],
                'keywords' => '',
                'sql' => 'No search performed.',
                'blacklisted' => false,
                'hash' => '',
                'simwordsnewsearch' => '',
                'simwords' => [],
                'time' => 0
            );
        }

        // ask cache
        if (rex_request('search_it_test', 'string', '') == '' && $this->cache AND $this->isCached($this->searchString)) {
            $this->cachedArray['time'] = microtime(true) - $startTime;

            if ($this->similarwords AND $this->cachedArray['count'] > 0) {
                $this->storeKeywords($this->searchArray);
            }

            // EP registrieren
            rex_extension::registerPoint(new rex_extension_point('SEARCH_IT_SEARCH_EXECUTED', $this->cachedArray));

            return $this->cachedArray;
        }


        if ($this->similarwords) {
            $simWordsSQL = rex_sql::factory();
            $simwordQuerys = [];
            foreach ($this->searchArray as $keyword) {
                $sounds = [];
                if ($this->similarwordsMode & SEARCH_IT_SIMILARWORDS_SOUNDEX) {
                    $sounds[] = "soundex = '" . soundex($keyword['search']) . "'";
                }

                if ($this->similarwordsMode & SEARCH_IT_SIMILARWORDS_METAPHONE) {
                    $sounds[] = "metaphone = '" . metaphone($keyword['search']) . "'";
                }

                if ($this->similarwordsMode & SEARCH_IT_SIMILARWORDS_COLOGNEPHONE) {
                    $sounds[] = "colognephone = '" . soundex_ger($keyword['search']) . "'";
                }
                $simwordQuerys[] = sprintf("
                  (SELECT
                    GROUP_CONCAT(DISTINCT keyword SEPARATOR ' ') as keyword,
                    %s AS typedin,
                    SUM(count) as count
                  FROM `%s`
                  WHERE 1
                    %s
                    AND (%s))",
                    $simWordsSQL->escape($keyword['search']),
                    $this->tempTablePrefix . 'search_it_keywords',
                    ($this->clang !== false) ? 'AND (clang = ' . intval($this->clang) . ' OR clang IS NULL)' : '',
                    implode(' OR ', $sounds)
                );
            }
            //echo '<br><pre>'; var_dump($simwordQuerys);echo '</pre>'; // Eine SQL-Abfrage pro Suchwort

            // simwords
            $simWordsSQL = rex_sql::factory();
            foreach ($simWordsSQL->getArray(sprintf("
                SELECT * FROM (%s) AS t
                %s
                ORDER BY count",
                    implode(' UNION ', $simwordQuerys),
                    $this->similarwordsPermanent ? '' : 'GROUP BY keyword, typedin'
                )
            ) as $simword) {
                //echo '<br><pre>'; var_dump($simword);echo '</pre>';
                $return['simwords'][$simword['typedin']] = array(
                    'keyword' => $simword['keyword'],
                    'typedin' => $simword['typedin'],
                    'count' => $simword['count'],
                );
            }
            /*echo '<br><pre>' .sprintf("
            SELECT * FROM (%s) AS t
            %s
            ORDER BY count",
                implode(' UNION ', $simwordQuerys),
                $this->similarwordsPermanent ? '' : 'GROUP BY keyword, typedin'
            ).'</pre>'; die();*/
            $newsearch = [];
            foreach ($this->searchArray as $keyword) {
                if (preg_match('~\s~isu', $keyword['search'])) {
                    $quotes = '"';
                } else {
                    $quotes = '';
                }

                if (array_key_exists($keyword['search'], $return['simwords'])) {
                    $newsearch[] = $quotes . $return['simwords'][$keyword['search']]['keyword'] . $quotes;
                } else {
                    $newsearch[] = $quotes . $keyword['search'] . $quotes;
                }
            }

            $return['simwordsnewsearch'] = implode(' ', $newsearch);
        }

        //print_r($this->searchArray);echo '<br><br>';
        if ($this->similarwordsPermanent) {
            $keywordCount = $this->parseSearchString($this->searchString . ' ' . $return['simwordsnewsearch']);
        }
        //echo '<br><pre>'; print_r($return['simwords']); echo '</pre>';


        $searchColumns = [];
        switch ($this->textMode) {
            case 'unmodified':
                $searchColumns[] = 'unchangedtext';
                break;

            case 'both':
                $searchColumns[] = 'plaintext';
                $searchColumns[] = 'unchangedtext';
                break;

            default:
                $searchColumns[] = 'plaintext';
        }


        $sql = rex_sql::factory();
        $A2Where = [];
        $Amatch = [];

        foreach ($this->searchArray as $searchword) {
            $AWhere = [];
            $similarkeywords = '';
            if ($this->similarwords && !isset($return['simwords'][$searchword['search']])) {
                continue;
            }
            if (isset($return['simwords'][$searchword['search']]['keyword'])) {
                $similarkeywords = $return['simwords'][$searchword['search']]['keyword'];
            }
            foreach ($this->searchArray as $keyword) {
                if ($keyword['search'] !== $searchword['search'] && !in_array($keyword['search'], explode(' ', $similarkeywords))) {
                    continue;
                }
                // build MATCH-Array
                $match = sprintf("(( MATCH (`%s`) AGAINST (%s)) * %d)", implode('`,`', $searchColumns), $sql->escape($keyword['search']), $keyword['weight']);
                if ($this->searchHtmlEntities) {
                    $match .= ' + ' . sprintf("(( MATCH (`%s`) AGAINST (%s)) * %d)", implode('`,`', $searchColumns), $sql->escape(htmlentities($keyword['search'], ENT_COMPAT, 'UTF-8')), $keyword['weight']);
                }
                $Amatch[] = $match;

                // build WHERE-Array
                if ($this->searchMode == 'match') {
                    $AWhere[] = $match;
                } else {
                    $tmpWhere = [];
                    foreach ($searchColumns as $searchColumn) {
                        $tmpWhere[] = sprintf("(`%s` LIKE '%%%s%%')", $searchColumn, str_replace(array('%', '_'), array('\%', '\_'), substr($sql->escape($keyword['search']), 1, -1)));

                        if ($this->searchHtmlEntities) {
                            $tmpWhere[] = sprintf("(`%s` LIKE '%%%s%%')", $searchColumn, str_replace(array('%', '_'), array('\%', '\_'), htmlentities($keyword['search'], ENT_COMPAT, 'UTF-8')));
                        }
                    }
                    $AWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
                }
                // echo '<br><pre>'; print_r($keyword); var_dump($AWhere);echo '</pre>';

                /*if($this->logicalMode == ' AND ')
                $Awhere[] = '+*'.$keyword['search'].'*';
                else
                $AWhere[] = '*'.$keyword['search'].'*';*/
            }
            $A2Where[] = '(' . implode(' OR ', $AWhere) . ')';
        }
        // build MATCH-String
        $match = '(' . implode(' + ', $Amatch) . ' + 1)';

        // build WHERE-String
        $where = '(' . implode($this->logicalMode, $A2Where) . ')';
        //$where = sprintf("( MATCH (%s) AGAINST ('%s' IN BOOLEAN MODE)) > 0",implode(',',$searchColumns),implode(' ',$Awhere));

        // language
        if ($this->clang !== false) {
            $where .= ' AND (clang = ' . intval($this->clang) . ' OR clang IS NULL)';
        }

        $AwhereToSearch = [];

        if (array_key_exists('articles', $this->searchInIDs) AND count($this->searchInIDs['articles'])) {
            $AwhereToSearch[] = "texttype = 'article'";
            $AwhereToSearch[] = "(fid IN (" . implode(',', $this->searchInIDs['articles']) . "))";
        }

		if(rex_addon::get('search_it')->getConfig('index_url_addon') && search_it_isUrlAddOnAvailable()) {
			if (array_key_exists('url', $this->searchInIDs) AND count($this->searchInIDs['url'])) {
				$AwhereToSearch[] = "texttype = 'url'";
				$AwhereToSearch[] = "(fid IN (" . implode(',', $this->searchInIDs['url']) . "))";
			}
		}

        if (array_key_exists('categories', $this->searchInIDs) AND count($this->searchInIDs['categories'])) {
            $AwhereToSearch[] = "(catid IN (" . implode(',', $this->searchInIDs['categories']) . ") AND ftable = '" . $this->tablePrefix . "article')";
        }

        if (array_key_exists('filecategories', $this->searchInIDs) AND count($this->searchInIDs['filecategories'])) {
            $AwhereToSearch[] = "(catid IN (" . implode(',', $this->searchInIDs['filecategories']) . ") AND ftable = '" . $this->tablePrefix . "media')";
        }

        if (array_key_exists('db_columns', $this->searchInIDs) AND count($this->searchInIDs['db_columns'])) {
            $AwhereToSearch[] = "texttype = 'db_column'";

            $Acolumns = [];

            foreach ($this->searchInIDs['db_columns'] as $table => $colArray) {
                foreach ($colArray as $column) {
                    //$Acolumns[] = sprintf("(ftable = '%s' AND fcolumn = '%s' %s)", $table, $column, $strsearchAllArticlesAnyway);
                    $Acolumns[] = sprintf("(ftable = '%s' AND fcolumn = '%s')", $table, $column);
                }
            }

            $AwhereToSearch[] = '(' . implode(' OR ', $Acolumns) . ')';
        }

        if (count($AwhereToSearch)) {
            if ($this->searchAllArticlesAnyway) {
                $where .= " AND ((texttype = 'article') OR (" . implode(' AND ', $AwhereToSearch) . '))';
            } else {
                $where .= ' AND (' . implode(' AND ', $AwhereToSearch) . ')';
            }
        }

        if (!empty($this->where)) {
            $where .= ' AND (' . $this->where . ')';
        }

        // build ORDER-BY-String
        $Aorder = [];
        foreach ($this->order as $col => $dir) {
            $Aorder[] = $col . ' ' . $dir;
        }

        $selectFields = [];
        if ($this->groupBy) {
            $selectFields[] = sprintf('(SELECT SUM%s FROM `%s` summe WHERE summe.fid = r1.fid AND summe.ftable = r1.ftable) AS RELEVANCE_SEARCH_IT', $match, $this->tempTablePrefix . 'search_it_index');
            $selectFields[] = sprintf('(SELECT COUNT(*) FROM `%s` summe WHERE summe.fid = r1.fid AND (summe.ftable IS NULL OR summe.ftable = r1.ftable) AND (summe.fcolumn IS NULL OR summe.fcolumn = r1.fcolumn) AND summe.texttype = r1.texttype) AS COUNT_SEARCH_IT', $this->tempTablePrefix . 'search_it_index');
        } else {
            $selectFields[] = $match . ' AS RELEVANCE_SEARCH_IT';
        }

        $selectFields[] = '`id`';
        $selectFields[] = '`fid`';
        $selectFields[] = '`catid`';
        $selectFields[] = '`ftable`';
        $selectFields[] = '`fcolumn`';
        $selectFields[] = '`texttype`';
        $selectFields[] = '`clang`';
        $selectFields[] = '`unchangedtext`';
        $selectFields[] = '`plaintext`';
        $selectFields[] = '`teaser`';
        $selectFields[] = '`values`';
        $selectFields[] = '`filename`';
        $selectFields[] = '`fileext`';

        if ($this->groupBy) {
            $query = sprintf('
            SELECT SQL_CALC_FOUND_ROWS %s
            FROM `%s` r1
            WHERE (%s) AND (
              (
                %s = (SELECT MAX%s FROM `%s` r2 WHERE r1.ftable = r2.ftable AND r1.fid = r2.fid %s)
                AND fid IS NOT NULL
              ) OR
              ftable IS NULL
            )
            GROUP BY ftable,fid,clang
            ORDER BY %s
            LIMIT %d,%d',
                implode(",\n", $selectFields),
                $this->tempTablePrefix . 'search_it_index',
                $where,
                $match,
                $match,
                $this->tempTablePrefix . 'search_it_index',
                ($this->clang !== false) ? 'AND (clang = ' . intval($this->clang) . ' OR clang IS NULL)' : '',
                implode(",\n", $Aorder),
                $this->limit[0], $this->limit[1]
            );
        } else {
            $query = sprintf('
            SELECT SQL_CALC_FOUND_ROWS %s
            FROM `%s`
            WHERE %s
            ORDER BY %s
            LIMIT %d,%d',
                implode(",\n", $selectFields),
                $this->tempTablePrefixv . 'search_it_index',
                $where,
                implode(",\n", $Aorder),
                $this->limit[0], $this->limit[1]
            );
        }
        //echo '<pre>'.$query.'</pre>';die();
        //echo '<pre>'.implode(",\n",$selectFields).'</pre>';
        try {
            $sqlResult = $sql->getArray($query);
            $info = 'Success';
        } catch (rex_sql_exception $e) {
            $sqlResult = [];
            $error = $e->getMessage();
            $return['errormessages'] .= $error;
        }

        $return['errormessages'] .= $this->errormessages;

        $indexIds = [];
        $count = 0;

        $sqlResultCount = $sql->getArray('SELECT FOUND_ROWS() as count');
        $return['count'] = intval($sqlResultCount[0]['count']);

        // hits
        $return['hits'] = [];
        $i = 0;
        foreach ($sqlResult as $hit) {
            $indexIds[] = $hit['id'];
            $return['hits'][$i] = [];
            $return['hits'][$i]['id'] = $hit['id'];
            $return['hits'][$i]['fid'] = $hit['fid'];
            if (!is_numeric($hit['fid']) AND !is_null($json_decode_fid = json_decode($hit['fid'], true))) {
                $return['hits'][$i]['fid'] = $json_decode_fid;
            }
            $return['hits'][$i]['table'] = $hit['ftable'];
            $return['hits'][$i]['column'] = $hit['fcolumn'];
            $return['hits'][$i]['type'] = $hit['texttype'];
            $return['hits'][$i]['clang'] = $hit['clang'];
            $return['hits'][$i]['unchangedtext'] = $hit['unchangedtext'];
            $return['hits'][$i]['plaintext'] = $hit['plaintext'];
            $return['hits'][$i]['teaser'] = $this->getTeaserText($hit['plaintext']);
            $return['hits'][$i]['highlightedtext'] = $this->getHighlightedText($hit['plaintext']);
            $return['hits'][$i]['article_teaser'] = $hit['teaser'];
            $return['hits'][$i]['values'] = search_it_config_unserialize($hit['values']);
            $return['hits'][$i]['filename'] = $hit['filename'];
            $return['hits'][$i]['fileext'] = $hit['fileext'];
            $i++;

            if ($this->groupBy) {
                $count += $hit['COUNT_SEARCH_IT'];
            }
        }

        if ($this->groupBy) {
            $indexIds = [];
            foreach ($sql->getArray(
                sprintf('
                SELECT id
                FROM `%s`
                WHERE %s
                LIMIT %d,%d',

                    $this->tempTablePrefix . 'search_it_index',
                    $where,
                    $this->limit[0], $count
                )
            ) as $hit) {
                $indexIds[] = $hit['id'];
            }
        }

        // keywords, which were searched for
        $return['keywords'] = $this->searchArray;
        $return['searchterm'] = $this->searchString;

        // sql
        $return['sql'] = $query;

        // was any blacklisted word searched for?
        $return['blacklisted'] = false;
        if (count($this->blacklisted) > 0) {
            $return['blacklisted'] = $this->blacklisted;
        }

        $return['hash'] = $this->cacheHash($this->searchString);


        // no test? then store keywords and cache
        if (rex_request('search_it_test', 'string', '') == '') {

            if ($this->similarwords AND $i) {
                $this->storeKeywords($this->searchArray);
            }
            // and not test =1 ??? oder doch mit cache?
            if ($this->cache) {
                $this->cacheSearch(serialize($return), $indexIds);
            }
        }
        // EP registrieren
        rex_extension::registerPoint(new rex_extension_point('SEARCH_IT_SEARCH_EXECUTED', $return));

        $return['time'] = microtime(true) - $startTime;

        return $return;
    }
}
