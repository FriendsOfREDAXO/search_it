<?php

/**
 * @deprecated Use the namespaced classes instead.
 */

use FriendsOfRedaxo\SearchIt\EventHandler;
use FriendsOfRedaxo\SearchIt\Helper\ArticleHelper;
use FriendsOfRedaxo\SearchIt\Helper\ColognePhonetic;
use FriendsOfRedaxo\SearchIt\Helper\FileHelper;
use FriendsOfRedaxo\SearchIt\Helper\FormBuilder;
use FriendsOfRedaxo\SearchIt\Helper\UrlAddon;
use FriendsOfRedaxo\SearchIt\Search\Highlighter;

/** @deprecated Use ArticleHelper::getArticles() */
function search_it_getArticles($cats = false, $sort = 'id'): array
{
    return ArticleHelper::getArticles($cats, $sort);
}

/** @deprecated Use ArticleHelper::getCategories() */
function search_it_getCategories($_ignoreoffline = true, $_onlyIDs = false, $_cats = false): array
{
    return ArticleHelper::getCategories($_ignoreoffline, $_onlyIDs, $_cats);
}

/** @deprecated Use FileHelper::getDirs() */
function search_it_getDirs($_startDir = '', $_getSubdirs = false): array
{
    return FileHelper::getDirs($_startDir, $_getSubdirs);
}

/** @deprecated Use FileHelper::getFiles() */
function search_it_getFiles($_startDir = '', $_fileexts = [], $_getSubdirs = false): array
{
    return FileHelper::getFiles($_startDir, $_fileexts, $_getSubdirs);
}

/** @deprecated Use EventHandler::handleExtensionPoint() */
function search_it_handle_extensionpoint($_ep): void
{
    EventHandler::handleExtensionPoint($_ep);
}

/** @deprecated Use FormBuilder::getSettingsFormSection() */
function search_it_getSettingsFormSection($id = '', $title = '&nbsp;', $elements = [], $ownsection = 'info', $collapse = false)
{
    return FormBuilder::getSettingsFormSection($id, $title, $elements, $ownsection, $collapse);
}

/** @deprecated */
function search_it_config_unserialize($_str)
{
    $conf = unserialize($_str);

    if (mb_strpos($_str, '\\"') === false) {
        return $conf;
    }

    $return = [];
    if (is_array($conf)) {
        foreach (unserialize($_str) as $k => $v) {
            if (is_array($v)) {
                $return[$k] = [];
                foreach ($v as $k2 => $v2) {
                    if (is_array($v2)) {
                        $return[$k][$k2] = [];
                        foreach ($v2 as $k3 => $v3) {
                            if (is_array($v3)) {
                                $return[$k][$k2][$k3] = [];
                                foreach ($v3 as $k4 => $v4) {
                                    $return[$k][$k2][$k3][$k4] = stripslashes($v4);
                                }
                            } else {
                                $return[$k][$k2][$k3] = stripslashes($v3);
                            }
                        }
                    } else {
                        $return[$k][$k2] = stripslashes($v2);
                    }
                }
            } else {
                $return[$k] = stripslashes($v);
            }
        }
    }

    return $return;
}

/** @deprecated Use ColognePhonetic::encode() */
function soundex_ger($word)
{
    return ColognePhonetic::encode($word);
}

/** @deprecated Use Highlighter::outputFilter() */
function search_it_search_highlighter_output($_ep): string
{
    return Highlighter::outputFilter($_ep);
}

/** @deprecated Use Highlighter::highlightText() */
function search_it_search_highlighter_getHighlightedText($_subject, $_searchString, $_tags): string
{
    return Highlighter::highlightText($_subject, $_searchString, $_tags);
}

/** @deprecated Use UrlAddon::isAvailable() */
function search_it_isUrlAddOnAvailable(): bool
{
    return UrlAddon::isAvailable();
}

/** @deprecated Use UrlAddon::getTableName() */
function search_it_getUrlAddOnTableName(): ?string
{
    return UrlAddon::getTableName();
}

/** @deprecated Use EventHandler::reindexColumns() */
function search_it_reindex_cols($_ep): bool
{
    return EventHandler::reindexColumns($_ep);
}
