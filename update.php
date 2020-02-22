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

if ( rex_sql_table::get(rex::getTable('search_it_cacheindex_ids'))->exists() && !rex_sql_table::get(rex::getTable(rex::getTempPrefix().'search_it_cacheindex_ids'))->exists() ) {
   rex_sql_table::get(rex::getTable('search_it_cacheindex_ids'))->setName(rex::getTable(rex::getTempPrefix().'search_it_cacheindex_ids'))->alter();
}
if ( rex_sql_table::get(rex::getTable('search_it_cache'))->exists() && !rex_sql_table::get(rex::getTable(rex::getTempPrefix().'search_it_cache'))->exists() ) {
    rex_sql_table::get(rex::getTable('search_it_cache'))->setName(rex::getTable(rex::getTempPrefix().'search_it_cache'))->alter();
}
if ( rex_sql_table::get(rex::getTable('search_it_index'))->exists() && !rex_sql_table::get(rex::getTable(rex::getTempPrefix().'search_it_index'))->exists() ) {
    rex_sql_table::get(rex::getTable('search_it_index'))->setName(rex::getTable(rex::getTempPrefix().'search_it_index'))->alter();
}
if ( rex_sql_table::get(rex::getTable('search_it_keywords'))->exists() && !rex_sql_table::get(rex::getTable(rex::getTempPrefix().'search_it_keywords'))->exists() ) {
    rex_sql_table::get(rex::getTable('search_it_keywords'))->setName(rex::getTable(rex::getTempPrefix().'search_it_keywords'))->alter();
}


