<?php

namespace FriendsOfRedaxo\SearchIt\Cronjob;

use FriendsOfRedaxo\SearchIt\Helper\UrlAddon;
use FriendsOfRedaxo\SearchIt\SearchIt;
use rex;
use rex_addon;
use rex_cronjob;
use rex_i18n;
use rex_sql;

class Reindex extends rex_cronjob
{
    public function execute(): bool
    {
        if (rex_addon::get('search_it')->isAvailable()) {
            $search_it = new SearchIt();
            $includeColumns = is_array(rex_addon::get('search_it')->getConfig('include')) ? rex_addon::get('search_it')->getConfig('include') : [];

            switch ($this->getParam('action')) {
                case 2:
                    foreach ($includeColumns as $table => $columnArray) {
                        foreach ($columnArray as $column) {
                            $search_it->indexColumn($table, $column);
                        }
                        if (count($columnArray) > 0) {
                            $search_it->deleteCache();
                        }
                    }
                    break;

                case 3:
                    $art_sql = rex_sql::factory();
                    $art_sql->setTable(rex::getTable('article'));
                    if ($art_sql->select('id,clang_id')) {
                        foreach ($art_sql->getArray() as $art) {
                            $search_it->indexArticle($art['id'], $art['clang_id']);
                        }
                        $search_it->deleteCache();
                    }
                    break;

                case 4:
                    if (rex_addon::get('search_it')->getConfig('index_url_addon') && UrlAddon::isAvailable()) {
                        $search_it->unindexDeletedURLs();
                        $search_it->indexNewURLs();
                        $search_it->indexUpdatedURLs();
                        $search_it->deleteCache();
                    }
                    break;

                case 1:
                default:
                    $search_it->generateIndex();
                    break;
            }

            return true;
        }

        $this->setMessage('Search it is not installed');
        return false;
    }

    public function getTypeName(): string
    {
        return rex_i18n::msg('search_it_reindex');
    }

    public function getParamFields(): array
    {
        return [
            [
                'label' => rex_i18n::msg('search_it_generate_actions_title'),
                'name' => 'action',
                'type' => 'select',
                'options' => [
                    1 => rex_i18n::msg('search_it_generate_full'),
                    2 => rex_i18n::msg('search_it_generate_columns'),
                    3 => rex_i18n::msg('search_it_generate_articles'),
                    4 => rex_i18n::msg('search_it_generate_urls'),
                ],
                'default' => '1',
                'notice' => rex_i18n::msg('search_it_generate_actions_title'),
            ],
        ];
    }
}
