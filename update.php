<?php
/**
 * Search it AddOn.
 * @author @tyrant88
 * @package search_it
 * @var rex_addon $addon
 */
$addon = rex_addon::get('search_it');

if ( $addon->pluginExists('reindex') ) {
    rex_dir::delete($addon->getPlugin('reindex')->getPath());
    //echo rex_view::warning($addon->i18n('search_it_settings_plugin_deleted'));
}
if ( $addon->pluginExists('search_highlighter') ) {
    rex_dir::delete($addon->getPlugin('search_highlighter')->getPath());
    //echo rex_view::warning($addon->i18n('search_it_settings_plugin_deleted'));
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

\rex_sql_table::get(
    \rex::getTable(rex::getTempPrefix().'search_it_index'))
    ->ensureColumn(new \rex_sql_column('lastindexed', 'VARCHAR(255)', TRUE))
    ->alter();

$addon->includeFile(__DIR__ . '/install.php');
