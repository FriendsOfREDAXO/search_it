<?php

namespace FriendsOfRedaxo\SearchIt\Helper;

use rex;
use rex_addon;
use rex_sql;

class ArticleHelper
{
    public static function getArticles($cats = false, string $sort = 'id'): array
    {
        $si = rex_addon::get('search_it');

        $whereCats = [];
        if (is_array($cats)) {
            $cats = array_map('intval', $cats);
            foreach ($cats as $catID) {
                $whereCats[] = "path LIKE '%|" . $catID . "|%'";
            }
        }

        $return = [];
        $query = 'SELECT id,name,path FROM ' . rex::getTable('article') . ' WHERE 1';
        if (!$si->getConfig('indexoffline')) {
            $query .= ' AND status = 1';
        }
        if (!empty($whereCats)) {
            $query .= ' AND (' . implode(' OR ', $whereCats) . ' OR (id IN (' . implode(',', $cats) . ')))';
        }
        $query .= ' GROUP BY id ORDER BY ' . $sort;

        $sql = rex_sql::factory();
        foreach ($sql->getArray($query) as $art) {
            $return[$art['id']] = $art['name'];
        }

        return $return;
    }

    public static function getCategories(bool $ignoreOffline = true, bool $onlyIDs = false, $cats = false): array
    {
        $si = rex_addon::get('search_it');

        $return = [];

        if (!empty($cats)) {
            $whereCats = [];
            $sqlCats = [];
            if (is_array($cats)) {
                foreach ($cats as $catID) {
                    $whereCats[] = "path LIKE '%|" . intval($catID) . "|%'";
                    $sqlCats[] = intval($catID);
                }
            }

            $query = 'SELECT id,catname,path FROM ' . rex::getTable('article') . ' WHERE startarticle = 1';
            if (!$si->getConfig('indexoffline') and $ignoreOffline) {
                $query .= ' AND status = 1';
            }
            if (!empty($whereCats)) {
                $query .= ' AND (' . implode(' OR ', $whereCats) . ' OR (id IN (' . implode(',', $sqlCats) . ')))';
            }
            $query .= ' GROUP BY id ORDER BY id';

            $sql = rex_sql::factory();
            foreach ($sql->getArray($query) as $cat) {
                if ($onlyIDs) {
                    $return[] = $cat['id'];
                } else {
                    $return[$cat['id']] = $cat['catname'];
                }
            }
        } else {
            $query = 'SELECT id,parent_id,catname,path FROM ' . rex::getTable('article') . ' WHERE startarticle = 1 AND parent_id=%d';
            if (!$si->getConfig('indexoffline') and $ignoreOffline) {
                $query .= ' AND status = 1';
            }
            $query .= ' GROUP BY id ORDER BY catpriority,id';

            $sql = rex_sql::factory();
            $catList = $sql->getArray(sprintf($query, 0));

            while (!empty($catList)) {
                $cat = array_shift($catList);
                if ($onlyIDs) {
                    $return[] = $cat['id'];
                } else {
                    $return[$cat['id']] = str_repeat('&nbsp;', mb_substr_count($cat['path'], '|') * 2 - 2) . $cat['catname'];
                }
                array_splice($catList, 0, 0, $sql->getArray(sprintf($query, $cat['id'])));
            }
        }

        return $return;
    }
}
