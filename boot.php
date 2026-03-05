<?php

use FriendsOfRedaxo\SearchIt\SearchIt;
use FriendsOfRedaxo\SearchIt\Cronjob\Reindex;
use FriendsOfRedaxo\SearchIt\Cronjob\ClearCache;
use FriendsOfRedaxo\SearchIt\EventHandler;
use FriendsOfRedaxo\SearchIt\Plaintext\PlaintextConverter;
use FriendsOfRedaxo\SearchIt\Search\Highlighter;

/**
 * @deprecated Use SearchIt::ART_*, SearchIt::URL_*, SearchIt::FILE_*, SearchIt::SIMILARWORDS_* instead.
 */
if (!defined('SEARCH_IT_ART_EXCLUDED')) {
    define('SEARCH_IT_ART_EXCLUDED', SearchIt::ART_EXCLUDED);
    define('SEARCH_IT_ART_IDNOTFOUND', SearchIt::ART_IDNOTFOUND);
    define('SEARCH_IT_ART_GENERATED', SearchIt::ART_GENERATED);
    define('SEARCH_IT_ART_REDIRECT', SearchIt::ART_REDIRECT);
    define('SEARCH_IT_ART_ERROR', SearchIt::ART_ERROR);
    define('SEARCH_IT_ART_NOTOK', SearchIt::ART_NOTOK);
    define('SEARCH_IT_ART_404', SearchIt::ART_404);

    define('SEARCH_IT_URL_EXCLUDED', SearchIt::URL_EXCLUDED);
    define('SEARCH_IT_URL_GENERATED', SearchIt::URL_GENERATED);
    define('SEARCH_IT_URL_REDIRECT', SearchIt::URL_REDIRECT);
    define('SEARCH_IT_URL_ERROR', SearchIt::URL_ERROR);
    define('SEARCH_IT_URL_NOTOK', SearchIt::URL_NOTOK);
    define('SEARCH_IT_URL_404', SearchIt::URL_404);

    define('SEARCH_IT_FILE_NOEXIST', SearchIt::FILE_NOEXIST);
    define('SEARCH_IT_FILE_XPDFERR_OPENSRC', SearchIt::FILE_XPDFERR_OPENSRC);
    define('SEARCH_IT_FILE_XPDFERR_OPENDEST', SearchIt::FILE_XPDFERR_OPENDEST);
    define('SEARCH_IT_FILE_XPDFERR_PERM', SearchIt::FILE_XPDFERR_PERM);
    define('SEARCH_IT_FILE_XPDFERR_OTHER', SearchIt::FILE_XPDFERR_OTHER);
    define('SEARCH_IT_FILE_FORBIDDEN_EXTENSION', SearchIt::FILE_FORBIDDEN_EXTENSION);
    define('SEARCH_IT_FILE_GENERATED', SearchIt::FILE_GENERATED);
    define('SEARCH_IT_FILE_EMPTY', SearchIt::FILE_EMPTY);

    define('SEARCH_IT_SIMILARWORDS_NONE', SearchIt::SIMILARWORDS_NONE);
    define('SEARCH_IT_SIMILARWORDS_SOUNDEX', SearchIt::SIMILARWORDS_SOUNDEX);
    define('SEARCH_IT_SIMILARWORDS_METAPHONE', SearchIt::SIMILARWORDS_METAPHONE);
    define('SEARCH_IT_SIMILARWORDS_COLOGNEPHONE', SearchIt::SIMILARWORDS_COLOGNEPHONE);
    define('SEARCH_IT_SIMILARWORDS_ALL', SearchIt::SIMILARWORDS_ALL);
}

$curDir = __DIR__;
require_once $curDir . '/functions/functions_search_it.php';

if (rex_request('search_highlighter', 'string', '') != '' && rex_addon::get('search_it')->getConfig('highlighterclass') != '') {
    rex_extension::register('OUTPUT_FILTER', [Highlighter::class, 'outputFilter']);
}

if (rex_addon::get('search_it')->getConfig('reindex_cols_onforms') == true) {
    rex_extension::register('REX_FORM_SAVED', [EventHandler::class, 'reindexColumns']);
    rex_extension::register('REX_YFORM_SAVED', [EventHandler::class, 'reindexColumns']);
    rex_extension::register('YFORM_SAVED', [EventHandler::class, 'reindexColumns']);
    rex_extension::register('YFORM_DATA_DELETED', [EventHandler::class, 'reindexColumns']);
    rex_extension::register('REX_FORM_DELETED', [EventHandler::class, 'reindexColumns']);
}
if (rex_addon::get('cronjob')->isAvailable() && !rex::isSafeMode()) {
    rex_cronjob_manager::registerType(Reindex::class);
    rex_cronjob_manager::registerType(ClearCache::class);
}

if (rex_request('search_it_build_index', 'string', '') != '') {
    rex_extension::register('ART_CONTENT', function (rex_extension_point $_ep) {
        $params = $_ep->getParams();
        $article_id = $params['article']->getArticleId();
        if (rex_request('search_it_build_index', 'string', '') == 'redirect') {
            $article_id = '';
        }
        $subject = '<!-- search_it ' . $article_id . ' -->' . $_ep->getSubject() . '<!-- /search_it ' . $article_id . ' -->';
        return $subject;

    });
}

if (rex_addon::get('search_it')->getConfig('index_url_addon') == true) {
    // automatic indexing of url addon urls: set trigger
    rex_extension::register('URL_TABLE_UPDATED', function () {
        rex_config::set('search_it', 'update_urls', true);
    });

    // automatic indexing of url addon urls: only set if current page is not indexing page to avoid cascading index actions
    if (rex_request('search_it_build_index', 'string', '') == '') {
        // automatic indexing of url addon urls: set trigger
        rex_extension::register('RESPONSE_SHUTDOWN', function () {
            if (rex_config::has('search_it', 'update_urls') && rex::isBackend()) {
                $search_it = new SearchIt();
                $search_it->unindexDeletedURLs();
                $search_it->indexNewURLs();
                $search_it->indexUpdatedURLs();
                $search_it->deleteCache();
                rex_config::remove('search_it', 'update_urls');
            }
        });
    }
}

if (rex::isBackend() && rex::getUser()) {
    // automatic indexing
    if (rex_addon::get('search_it')->getConfig('automaticindex') == true) {
        $extensionPoints = array(
            'ART_DELETED',
            'ART_META_UPDATED',
            'ART_STATUS',
            'ART_ADDED',
            'ART_UPDATED',
            'CAT_DELETED',
            'CAT_STATUS',
            'CAT_ADDED',
            'CAT_UPDATED',
            'MEDIA_ADDED',
            'MEDIA_UPDATED',
            'MEDIA_DELETED',
            'SLICE_ADDED',
            'SLICE_DELETED',
            'SLICE_UPDATED',
        );
        rex_extension::register($extensionPoints, [EventHandler::class, 'handleExtensionPoint']);
    }

    if (strpos(rex_request('page', 'string', ''), 'search_it') !== false) {
        rex_view::addJsFile($this->getAssetsUrl('search_it.js'));
        rex_view::addCssFile($this->getAssetsUrl('search_it.css'));
    }

    //set default Values on installation
    if (!$this->hasConfig('modus')) {
        $this->setConfig(array(
            'modus' => 'keywords',
            'maxSuggestion' => 10,
            'similarwordsmode' => '0',
            'autoSubmitForm' => 1
        ));
    }
    if (!$this->hasConfig('plainOrder')) {
        $this->setConfig([
            'plainOrder' => 'selectors,regex,textile,striptags',
            'selectors' => "head,\nscript",
            'regex' => '',
            'textile' => true,
            'striptags' => true,
            'processparent' => false,
            'plainText' => false,
        ]);
    }
    if (!$this->hasConfig('stats')) {
        $this->setConfig(array(
            'maxtopsearchitems' => 10,
            'searchtermselect' => '',
            'searchtermselectmonthcount' => 12,
            'stats' => 0,
        ));
    }
    if (!$this->hasConfig('limit')) {
        $this->setConfig(array(
            'limit' => [0, 10],
            'maxSuggestion' => '10'
        ));
    }
}

// former plugins
// autocomplete
if ($this->getConfig('autoComplete') == 1) {
    if (rex::isBackend()) {

        rex_view::addCssFile($this->getAssetsUrl('suggest.css'));
        rex_view::addJsFile($this->getAssetsUrl('suggest.js'));

    } else {
        if ($this->getConfig('autoSubmitForm') == 1) {
            rex_extension::register('OUTPUT_FILTER', function (rex_extension_point $ep) {
                $subject = $ep->getSubject();
                return str_replace(['search_it-form', '###AUTOSUBMIT###'],
                    ['search_it-form search_it-form-autocomplete', ''],
                    $subject);
            });
        }
    }
}

// plaintext
if ($this->getConfig('plaintext') == 1) {
    require_once __DIR__ . '/functions/functions_plaintext.php';

    if (rex::isBackend()) {
        rex_extension::register('SEARCH_IT_PLAINTEXT', [PlaintextConverter::class, 'extensionPointHandler']);
    }
}

// stats
if (!rex_plugin::get('search_it', 'stats')->isAvailable()) {
    require_once __DIR__ . '/functions/functions_stats.php';
}
if ($this->getConfig('stats') == 1) {
    if (rex_request('search_it_test', 'string', '') == '') {
        rex_extension::register('SEARCH_IT_SEARCH_EXECUTED', 'search_it_stats_storekeywords');
    }
    if (rex::isBackend()) {
        rex_extension::register('SEARCH_IT_PAGE_MAINTENANCE', 'search_it_stats_addtruncate');

        rex_view::addCssFile($this->getAssetsUrl('stats.css'));
    }
}

/**
 * Backward compatibility class aliases.
 * @deprecated Use the namespaced classes instead.
 */
class_alias(SearchIt::class, 'search_it');
class_alias(FriendsOfRedaxo\SearchIt\Api\Autocomplete::class, 'rex_api_search_it_autocomplete');
class_alias(FriendsOfRedaxo\SearchIt\Stats\Statistics::class, 'search_it_stats');
class_alias(FriendsOfRedaxo\SearchIt\Pdf\PdfConverter::class, 'pdf2txt');
class_alias(Reindex::class, 'rex_cronjob_reindex');
class_alias(ClearCache::class, 'rex_cronjob_clearcache');
class_alias(FriendsOfRedaxo\SearchIt\Console\ReindexCommand::class, 'rex_search_it_command_reindex');
class_alias(FriendsOfRedaxo\SearchIt\Console\ClearCacheCommand::class, 'rex_search_it_command_clearcache');
