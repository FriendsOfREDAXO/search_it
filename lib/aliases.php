<?php

/**
 * Backward compatibility aliases for search_it.
 *
 * @deprecated Use the namespaced classes instead.
 */

class_alias(FriendsOfRedaxo\SearchIt\SearchIt::class, 'search_it');
class_alias(FriendsOfRedaxo\SearchIt\Api\Autocomplete::class, 'rex_api_search_it_autocomplete');
class_alias(FriendsOfRedaxo\SearchIt\Stats\Statistics::class, 'search_it_stats');
class_alias(FriendsOfRedaxo\SearchIt\Pdf\PdfConverter::class, 'pdf2txt');
class_alias(FriendsOfRedaxo\SearchIt\Cronjob\Reindex::class, 'rex_cronjob_reindex');
class_alias(FriendsOfRedaxo\SearchIt\Cronjob\ClearCache::class, 'rex_cronjob_clearcache');
class_alias(FriendsOfRedaxo\SearchIt\Console\ReindexCommand::class, 'rex_search_it_command_reindex');
class_alias(FriendsOfRedaxo\SearchIt\Console\ClearCacheCommand::class, 'rex_search_it_command_clearcache');
