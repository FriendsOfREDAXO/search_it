<?php

namespace FriendsOfRedaxo\SearchIt;

use FriendsOfRedaxo\SearchIt\Helper\ArticleHelper;
use rex;
use rex_addon;
use rex_extension_point;
use rex_logger;

class EventHandler
{
    public static function handleExtensionPoint(rex_extension_point $ep): void
    {
        $si = rex_addon::get('search_it');

        $params = $ep->getParams();
        $includeColumns = is_array($si->getConfig('include')) ? $si->getConfig('include') : [];
        $search_it = new SearchIt();

        switch ($ep->getName()) {
            case 'ART_DELETED':
                $search_it->unindexArticle($params['id']);
                break;

            case 'ART_META_UPDATED':
            case 'ART_ADDED':
            case 'ART_UPDATED':
                foreach ($includeColumns as $table => $columnArray) {
                    if ($table == rex::getTable('article')) {
                        foreach ($columnArray as $column) {
                            $search_it->indexColumn($table, $column, 'id', $params['id']);
                        }
                    }
                }
                $search_it->deleteCache();
                break;

            case 'ART_STATUS':
                if ($params['status'] || $si->getConfig('indexoffline')) {
                    $search_it->indexArticle($params['id'], $params['clang'], true);
                } else {
                    $search_it->unindexArticle($params['id'], $params['clang']);
                }

                foreach ($includeColumns as $table => $columnArray) {
                    if ($table == rex::getTable('article')) {
                        foreach ($columnArray as $column) {
                            $search_it->indexColumn($table, $column, 'id', $params['id']);
                        }
                        if (count($columnArray) > 0) {
                            $search_it->deleteCache();
                        }
                    }
                }
                break;

            case 'CAT_DELETED':
                break;

            case 'CAT_STATUS':
                if ($params['status'] || $si->getConfig('indexoffline')) {
                    foreach (ArticleHelper::getArticles([$params['id']]) as $art_id => $art_name) {
                        $search_it->indexArticle($art_id, $params['clang']);
                    }
                    $search_it->deleteCache();
                } else {
                    $search_it->unindexArticle($params['id'], $params['clang']);
                    foreach (ArticleHelper::getArticles([$params['id']]) as $art_id => $art_name) {
                        $search_it->unindexArticle($art_id, $params['clang']);
                    }
                }

                foreach ($includeColumns as $table => $columnArray) {
                    if ($table == rex::getTable('article')) {
                        foreach ($columnArray as $column) {
                            $search_it->indexColumn($table, $column, 'id', $params['id']);
                        }
                        if (count($columnArray) > 0) {
                            $search_it->deleteCache();
                        }
                    }
                }
                break;

            case 'CAT_ADDED':
            case 'CAT_UPDATED':
                foreach ($includeColumns as $table => $columnArray) {
                    if ($table == rex::getTable('article')) {
                        foreach ($columnArray as $column) {
                            $search_it->indexColumn($table, $column, 'id', $params['id']);
                        }
                        if (count($columnArray) > 0) {
                            $search_it->deleteCache();
                        }
                    }
                }
                break;

            case 'MEDIA_ADDED':
            case 'MEDIA_DELETED':
                foreach ($includeColumns as $table => $columnArray) {
                    if ($table == rex::getTable('media')) {
                        foreach ($columnArray as $column) {
                            $search_it->indexColumn($table, $column);
                        }
                        if (count($columnArray) > 0) {
                            $search_it->deleteCache();
                        }
                    }
                }
                break;

            case 'MEDIA_UPDATED':
                foreach ($includeColumns as $table => $columnArray) {
                    if ($table == rex::getTable('media')) {
                        foreach ($columnArray as $column) {
                            $search_it->indexColumn($table, $column, 'id', $params['id']);
                        }
                        if (count($columnArray) > 0) {
                            $search_it->deleteCache();
                        }
                    }
                }
                break;

            case 'SLICE_UPDATED':
            case 'SLICE_DELETED':
            case 'SLICE_ADDED':
                $search_it->indexArticle($params['article_id'], $params['clang'], true);
                break;
        }
    }

    public static function reindexColumns(rex_extension_point $ep): bool
    {
        if ($ep->getSubject() instanceof \Exception) {
            return $ep->getSubject();
        }

        $params = $ep->getParams();

        $includeColumns = is_array(rex_addon::get('search_it')->getConfig('include')) ? rex_addon::get('search_it')->getConfig('include') : [];
        $search_it = new SearchIt();

        $didcol = false;
        $did = false;
        $tablename = '';

        if (!empty($params['yform'])) {
            $tablename = $params['form']->params['main_table'];
            $didcol = 'id';
            $did = $params['id'];
        } elseif (!empty($params['form'])) {
            $tablename = $params['form']->getTableName();
        } elseif (!empty($params['table'])) {
            $tablename = $params['table']->getTableName();
            $didcol = 'id';
            $did = $params['data_id'];
        } else {
            rex_logger::factory()->info('keine Angabe welche Tabelle indexiert werden soll');
            return false;
        }

        if (!array_key_exists($tablename, $includeColumns) or !is_array($includeColumns[$tablename])) {
            return true;
        }

        foreach ($includeColumns[$tablename] as $col) {
            $search_it->indexColumn($tablename, $col, $didcol, $did, false, false, true);
        }

        return true;
    }
}
