<?php
/**
 * Search it AddOn.
 * @author @tyrant88
 * @package search_it
 * @var rex_addon $this
 */
if ( is_dir($this->getPlugin('reindex')->getPath()) ) {
    rex_dir::delete($this->getPlugin('reindex')->getPath());
    //echo rex_view::warning($this->i18n('search_it_settings_plugin_deleted'));
}
if ( is_dir($this->getPlugin('search_highlighter')->getPath()) ) {
    rex_dir::delete($this->getPlugin('search_highlighter')->getPath());
    //echo rex_view::warning($this->i18n('search_it_settings_plugin_deleted'));
}

$result = rex_sql::factory()->getArray("SELECT count(*) as `count`
FROM information_schema.TABLES
WHERE (TABLE_NAME = 'rex_search_it_index')");

if($result[0]['count'] > 0) {
    rex_sql::factory()->setQuery("RENAME TABLE rex_search_it_cache TO rex_tmp_search_it_cache");
    rex_sql::factory()->setQuery("RENAME TABLE rex_search_it_cacheindex_ids TO rex_tmp_search_it_cacheindex_ids");
    rex_sql::factory()->setQuery("RENAME TABLE rex_search_it_index TO rex_tmp_search_it_index");
    rex_sql::factory()->setQuery("RENAME TABLE rex_search_it_keywords TO rex_tmp_search_it_keywords");
}