<?php

class rex_api_search_it_autocomplete_getSimilarWords extends rex_api_function
{

    protected $published = true;

    function execute()
    {

        error_reporting(0);
        header("Content-Type: text/html; charset=utf-8");

        $q = rex_request('q', 'string', false);

        //modus für die ausgabe
        $plugin = rex_plugin::get('search_it', 'autocomplete');

        $modus = $plugin->getConfig('modus');
        $maxSuggestion = $plugin->getConfig('maxSuggestion');
        $similarWordsMode = $plugin->getConfig('similarwordsmode');

        if ($q != '') {

            if ($modus == "highlightedtext" || $modus == "articlename") {


                $search_it = new search_it();
                $result = $search_it->search($request);

                //$search_it->similarwordsMode = false; //keine speichern der keywords pro eingabe
                $search_it->setMaxHighlightedTextChars(20);
                //$search_it->ellipsis = '';
                //$search_it->cache = false;
                //$search_it->setHighlightType("surroundtext");
                $result = $search_it->search($q);

                if ($result['count'] > 0) {
                    $ids = [];

                    foreach ($result['hits'] as $hit) {

                        if (!in_array($hit['fid'], $ids)) {

                            $article = rex_article::get($hit['fid']);

                            if ($hit['type'] == 'db_column' and $hit['table'] == rex::getTable('article')) {

                                $text = $hit['article_teaser'];

                            } else {

                                $text = $hit['highlightedtext'];

                                $ids[] = $hit['fid'];

                                if ($modus == "highlightedtext") {
                                    echo $text;
                                } elseif ($modus == "articlename") {
                                    echo $article->getName();
                                }
                                echo "\n";
                            }
                        }

                        if (count($ids) >= $maxSuggestion)
                            break;
                    }//enforeach
                }//ifresults

            } //modus
            elseif ($modus == "keywords") {

                // 0 = Deaktiviert , 1 = soundex, 2 =  metaphone, 7 = ALL

                $db = rex_sql::factory();
                $db->setDebug(false);

                if ($similarWordsMode == '0') {

                    $sql = sprintf("
              SELECT keyword FROM `%s` WHERE ( keyword LIKE :keyword ) AND (clang = -1 OR clang = :clang) GROUP BY keyword ORDER BY count ",
                        rex::getTablePrefix() . rex::getTempPrefix() . 'search_it_keywords',
                    );

                    $params = [
                        'keyword' => str_replace(['_', '%'], ['\_', '\%'], $q) . '%',
                        'clang' => rex_clang::getCurrentId(),
                    ];
                }

                if ($similarWordsMode == '1') {

                    $sql = sprintf("
              SELECT keyword FROM `%s` WHERE ( keyword LIKE :keyword OR soundex = :soundex  ) AND (clang = -1 OR clang = :clang) GROUP BY keyword ORDER BY count ",
                        rex::getTablePrefix() . rex::getTempPrefix() . 'search_it_keywords',
                    );

                    $params = [
                        'keyword' => str_replace(['_', '%'], ['\_', '\%'], $q) . '%',
                        'soundex' => soundex($q),
                        'clang' => rex_clang::getCurrentId(),
                    ];
                }

                if ($similarWordsMode == '2') {

                    $sql = sprintf("
              SELECT keyword FROM `%s` WHERE ( keyword LIKE :keyword OR metaphone = :metaphone  ) AND (clang = -1 OR clang = :clang) GROUP BY keyword ORDER BY count ",
                        rex::getTablePrefix() . rex::getTempPrefix() . 'search_it_keywords',
                    );

                    $params = [
                        'keyword' => str_replace(['_', '%'], ['\_', '\%'], $q) . '%',
                        'metaphone' => metaphone($q),
                        'clang' => rex_clang::getCurrentId(),
                    ];
                }

                if ($similarWordsMode == '3') {

                    $sql = sprintf("
              SELECT keyword FROM `%s` WHERE ( keyword LIKE :keyword OR colognephone = :soundex_ger  ) AND (clang = -1 OR clang = :clang) GROUP BY keyword ORDER BY count ",
                        rex::getTablePrefix() . rex::getTempPrefix() . 'search_it_keywords',
                    );

                    $params = [
                        'keyword' => str_replace(['_', '%'], ['\_', '\%'], $q) . '%',
                        'soundex_ger' => soundex_ger($q),
                        'clang' => rex_clang::getCurrentId(),
                    ];
                }

                if ($similarWordsMode == '7') {

                    $sql = sprintf("
              SELECT keyword FROM `%s` WHERE ( keyword LIKE :keyword OR soundex = :soundex OR metaphone = :metaphone OR colognephone = :soundex_ger) AND (clang = -1 OR clang = :clang) GROUP BY keyword ORDER BY count ",
                        rex::getTablePrefix() . rex::getTempPrefix() . 'search_it_keywords',
                    );

                    $params = [
                        'keyword' => str_replace(['_', '%'], ['\_', '\%'], $q) . '%',
                        'soundex' => soundex($q),
                        'metaphone' => metaphone($q),
                        'soundex_ger' => soundex_ger($q),
                        'clang' => rex_clang::getCurrentId(),
                    ];
                }

                $db->setQuery($sql, $params);

                if ($db->getRows() > 0) {
                    for ($i = 0; $i < $db->getRows(); $i++) {

                        echo $db->getValue("keyword") . "\n";

                        if ($i >= $maxSuggestion - 1) break;

                        $db->next();
                    }
                }
            }//endifmodus

            exit;

        } else {
            echo 'empty q';

            exit;

        }//ifempty

    }
}
