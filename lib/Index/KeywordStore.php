<?php

namespace FriendsOfRedaxo\SearchIt\Index;

use FriendsOfRedaxo\SearchIt\Helper\ColognePhonetic;
use FriendsOfRedaxo\SearchIt\SearchIt;
use rex;
use rex_sql;
use rex_sql_exception;

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
        $excluded = array_flip(array_merge($this->blacklist, $this->stopwords));
        foreach ($keywords as $keyword) {
            $clang = (isset($keyword['clang']) && $keyword['clang'] !== false) ? (int) $keyword['clang'] : -1;
            $lowerKeyword = mb_strtolower($keyword['search'], 'UTF-8');

            // without counting, repeated keywords would only produce the same row again
            $key = $clang . '|' . $lowerKeyword;
            if (!$doCount && isset($simWords[$key])) {
                continue;
            }

            if (!isset($excluded[$lowerKeyword]) && !is_numeric($keyword['search'])) {
                $soundex = ($this->similarwordsMode & SearchIt::SIMILARWORDS_SOUNDEX) ? soundex($keyword['search']) : '';
                $metaphone = ($this->similarwordsMode & SearchIt::SIMILARWORDS_METAPHONE) ? metaphone($keyword['search']) : '';
                $colognephone = ($this->similarwordsMode & SearchIt::SIMILARWORDS_COLOGNEPHONE) ? ColognePhonetic::encode($keyword['search']) : '';

                $row = sprintf(
                    "(%s, %s, %s, %s, %s)",
                    $simWordsSQL->escape($keyword['search']),
                    $simWordsSQL->escape($soundex !== '0000' ? $soundex : ''),
                    $simWordsSQL->escape($metaphone),
                    $simWordsSQL->escape($colognephone),
                    $clang
                );
                if ($doCount) {
                    $simWords[] = $row;
                } else {
                    $simWords[$key] = $row;
                }
            }
        }
        $simWords = array_values($simWords);

        if (!empty($simWords)) {
            $simWordsTeile = array_chunk($simWords, $this->mysqlInsertChunkSize);
            foreach ($simWordsTeile as $simWordsTeil) {
                $query = sprintf(
                    "INSERT INTO `%s`
                    (keyword, soundex, metaphone, colognephone, clang)
                    VALUES
                    %s
                    ON DUPLICATE KEY UPDATE count = count + %d",
                    self::getTempTablePrefix() . 'search_it_keywords',
                    implode(',', $simWordsTeil),
                    $doCount ? 1 : 0
                );

                for ($attempt = 1; $attempt <= 3; ++$attempt) {
                    try {
                        $simWordsSQL->setQuery($query);
                        break;
                    } catch (rex_sql_exception $e) {
                        if ($attempt < 3 && str_contains($e->getMessage(), 'Deadlock')) {
                            usleep(50_000 * $attempt);
                            continue;
                        }
                        throw $e;
                    }
                }
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
