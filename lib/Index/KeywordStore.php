<?php

namespace FriendsOfRedaxo\SearchIt\Index;

use FriendsOfRedaxo\SearchIt\Helper\ColognePhonetic;
use rex;
use rex_sql;

class KeywordStore
{
    private int $similarwordsMode;
    private array $blacklist;
    private array $stopwords;
    private int $mysqlInsertChunkSize;

    public function __construct(int $similarwordsMode, array $blacklist, array $stopwords, int $mysqlInsertChunkSize = 100)
    {
        $this->similarwordsMode = $similarwordsMode;
        $this->blacklist = $blacklist;
        $this->stopwords = $stopwords;
        $this->mysqlInsertChunkSize = $mysqlInsertChunkSize;
    }

    private static function getTempTablePrefix(): string
    {
        static $tempTablePrefix = null;
        if ($tempTablePrefix === null) {
            $tempTablePrefix = rex::getTablePrefix() . rex::getTempPrefix();
        }
        return $tempTablePrefix;
    }

    /**
     * Stores keywords for similarity search.
     *
     * @param array $keywords Array of ['search' => string, 'clang' => int|false]
     * @param bool $doCount Whether to increment the count on duplicate
     */
    public function storeKeywords(array $keywords, bool $doCount = true): void
    {
        $simWordsSQL = rex_sql::factory();
        $simWords = [];
        foreach ($keywords as $keyword) {
            if (!in_array(mb_strtolower($keyword['search'], 'UTF-8'), $this->blacklist) &&
                !in_array(mb_strtolower($keyword['search'], 'UTF-8'), $this->stopwords) &&
                !is_numeric($keyword['search'])
            ) {
                $simWords[] = sprintf(
                    "(%s, %s, %s, %s, %s)",
                    $simWordsSQL->escape($keyword['search']),
                    $simWordsSQL->escape((($this->similarwordsMode & \SEARCH_IT_SIMILARWORDS_SOUNDEX) && !is_numeric(soundex($keyword['search']))) ? soundex($keyword['search']) : ''),
                    $simWordsSQL->escape((($this->similarwordsMode & \SEARCH_IT_SIMILARWORDS_METAPHONE) && !is_numeric(metaphone($keyword['search']))) ? metaphone($keyword['search']) : ''),
                    $simWordsSQL->escape((($this->similarwordsMode & \SEARCH_IT_SIMILARWORDS_COLOGNEPHONE) && !is_numeric(ColognePhonetic::encode($keyword['search']))) ? ColognePhonetic::encode($keyword['search']) : ''),
                    (isset($keyword['clang']) && $keyword['clang'] !== false) ? (int) $keyword['clang'] : '-1'
                );
            }
        }

        if (!empty($simWords)) {
            $simWordsTeile = array_chunk($simWords, $this->mysqlInsertChunkSize);
            foreach ($simWordsTeile as $simWordsTeil) {
                $simWordsSQL->setQuery(
                    sprintf(
                        "INSERT INTO `%s`
                        (keyword, soundex, metaphone, colognephone, clang)
                        VALUES
                        %s
                        ON DUPLICATE KEY UPDATE count = count + %d",
                        self::getTempTablePrefix() . 'search_it_keywords',
                        implode(',', $simWordsTeil),
                        $doCount ? 1 : 0
                    )
                );
            }
        }
    }

    /**
     * Deletes all stored keywords.
     */
    public function deleteKeywords(): void
    {
        $kw_sql = rex_sql::factory();
        $kw_sql->setQuery(sprintf('TRUNCATE TABLE `%s`', self::getTempTablePrefix() . 'search_it_keywords'));
    }
}
