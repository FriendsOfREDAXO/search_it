<?php

namespace FriendsOfRedaxo\SearchIt\Helper;

use rex_addon;
use rex_sql;
use rex_version;

class UrlAddon
{
    public static function isAvailable(): bool
    {
        return rex_addon::get('url')->isAvailable()
            && rex_version::compare(rex_addon::get('url')->getVersion(), '1.5', '>=');
    }

    public static function getTableName(): ?string
    {
        if (!self::isAvailable()) {
            return null;
        }

        $sql = rex_sql::factory();
        $allTables = $sql->getTables();

        foreach ($allTables as $oneTable) {
            if (mb_strpos($oneTable, \Url\UrlManagerSql::TABLE_NAME) !== false) {
                return $oneTable;
            }
        }

        return null;
    }
}
