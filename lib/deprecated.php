<?php

class search_it extends \FriendsOfRedaxo\SearchIt\SearchIt
{
}

class search_it_stats extends \FriendsOfRedaxo\SearchIt\Stats\Statistics
{
}

class pdf2txt extends \FriendsOfRedaxo\SearchIt\Pdf\PdfConverter
{
}

class rex_search_it_command_reindex extends \FriendsOfRedaxo\SearchIt\Console\ReindexCommand
{
}

class rex_search_it_command_clearcache extends \FriendsOfRedaxo\SearchIt\Console\ClearCacheCommand
{
}

if (class_exists('rex_cronjob')) {
    class rex_cronjob_reindex extends \FriendsOfRedaxo\SearchIt\Cronjob\Reindex
    {
    }

    class rex_cronjob_clearcache extends \FriendsOfRedaxo\SearchIt\Cronjob\ClearCache
    {
    }
}
