<?php

namespace FriendsOfRedaxo\SearchIt\Helper;

use rex_addon;
use rex_logger;

class Logger
{
    private const CHANNEL_ARTICLES = 'logging_articles';
    private const CHANNEL_URLS = 'logging_urls';

    private static function isEnabled(string $channel): bool
    {
        return (bool) rex_addon::get('search_it')->getConfig($channel, true);
    }

    public static function logArticle(string $level, string $message): void
    {
        if (self::isEnabled(self::CHANNEL_ARTICLES)) {
            rex_logger::factory()->log($level, $message);
        }
    }

    public static function logUrl(string $level, string $message): void
    {
        if (self::isEnabled(self::CHANNEL_URLS)) {
            rex_logger::factory()->log($level, $message);
        }
    }
}
