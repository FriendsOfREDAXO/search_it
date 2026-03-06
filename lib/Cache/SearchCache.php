<?php

namespace FriendsOfRedaxo\SearchIt\Cache;

use rex;
use rex_sql;
use rex_sql_exception;
use rex_view;

class SearchCache
{
    private static function getTempTablePrefix(): string
    {
        static $tempTablePrefix = null;
        if ($tempTablePrefix === null) {
            $tempTablePrefix = rex::getTablePrefix() . rex::getTempPrefix();
        }
        return $tempTablePrefix;
    }

    /**
     * Returns if a search term is already cached.
     * The cached result will be stored in the passed array.
     */
    public function isCached(string $hash, array &$cachedArray): bool
    {
        $sql = rex_sql::factory();
        $results = $sql->getArray(
            'SELECT returnarray FROM ' . self::getTempTablePrefix() . 'search_it_cache WHERE hash = :hash',
            ['hash' => $hash]
        );

        foreach ($results as $value) {
            $decoded = json_decode($value['returnarray'], true);
            if ($decoded !== false && $decoded !== null) {
                $cachedArray = $decoded;
                return true;
            }
            return false;
        }

        return false;
    }

    /**
     * Calculates the cache hash.
     */
    public function cacheHash(string $searchString, string $hashMe): string
    {
        return md5($searchString . $hashMe);
    }

    /**
     * Stores a search result in the cache.
     */
    public function cacheSearch(string $hash, string $resultJson, array $indexIds): bool
    {
        $sql = rex_sql::factory();
        $sql->setTable(self::getTempTablePrefix() . 'search_it_cache');
        $sql->setValues([
            'hash' => $hash,
            'returnarray' => $resultJson,
        ]);
        $sql->insert();
        $lastId = $sql->getLastId();

        $Ainsert = [];
        foreach ($indexIds as $id) {
            $Ainsert[] = sprintf('(%d,%d)', $id, $lastId);
        }

        if (!empty($Ainsert) && implode(',', $Ainsert) != '') {
            $sql2 = rex_sql::factory();

            try {
                $sql2->setQuery(
                    sprintf(
                        'INSERT INTO `%s` (index_id,cache_id) VALUES %s;',
                        self::getTempTablePrefix() . 'search_it_cacheindex_ids',
                        implode(',', $Ainsert)
                    )
                );
                return true;
            } catch (rex_sql_exception $e) {
                $error = $e->getMessage();
                echo rex_view::warning($error);
                return false;
            }
        }

        return false;
    }

    /**
     * Truncates the cache or deletes all data that are concerned with the given index-ids.
     *
     * @param int[]|false $indexIds
     */
    public function deleteCache(array|false $indexIds = false): void
    {
        if ($indexIds === false) {
            $delete = rex_sql::factory();
            if ($delete->inTransaction()) {
                $delete->setQuery('DELETE FROM ' . self::getTempTablePrefix() . 'search_it_cacheindex_ids');
                $delete->setQuery('DELETE FROM ' . self::getTempTablePrefix() . 'search_it_cache');
            } else {
                $delete->setQuery('TRUNCATE ' . self::getTempTablePrefix() . 'search_it_cacheindex_ids');
                $delete->setQuery('TRUNCATE ' . self::getTempTablePrefix() . 'search_it_cache');
            }
        } elseif (!empty($indexIds)) {
            $sql = rex_sql::factory();

            $query = sprintf(
                'SELECT cache_id FROM %s WHERE index_id IN (%s)',
                self::getTempTablePrefix() . 'search_it_cacheindex_ids',
                implode(',', array_map('intval', $indexIds))
            );

            $deleteIds = [0];
            foreach ($sql->getArray($query) as $cacheId) {
                $deleteIds[] = (int) $cacheId['cache_id'];
            }

            $delete = rex_sql::factory();
            $delete->setTable(self::getTempTablePrefix() . 'search_it_cache');
            $delete->setWhere('id IN (' . implode(',', $deleteIds) . ')');
            $delete->delete();

            $delete2 = rex_sql::factory();
            $delete2->setTable(self::getTempTablePrefix() . 'search_it_cacheindex_ids');
            $delete2->setWhere('cache_id IN (' . implode(',', $deleteIds) . ')');
            $delete2->delete();

            $delete3 = rex_sql::factory();
            $delete3->setTable(self::getTempTablePrefix() . 'search_it_cache');
            $delete3->setWhere(sprintf(
                'id NOT IN (SELECT cache_id FROM `%s`)',
                self::getTempTablePrefix() . 'search_it_cacheindex_ids'
            ));
            $delete3->delete();
        }
    }
}
