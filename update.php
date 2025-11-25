<?php
/**
 * Search it AddOn.
 * @package search_it
 * @var rex_addon $addon
 */
$addon = rex_addon::get('search_it');

rex_dir::delete(rex_path::addon('search_it','plugins'),true);

if (rex_sql_table::get(rex::getTable('search_it_cacheindex_ids'))->exists() && !rex_sql_table::get(rex::getTable(rex::getTempPrefix() . 'search_it_cacheindex_ids'))->exists()) {
    rex_sql_table::get(rex::getTable('search_it_cacheindex_ids'))->setName(rex::getTable(rex::getTempPrefix() . 'search_it_cacheindex_ids'))->alter();
}
if (rex_sql_table::get(rex::getTable('search_it_cache'))->exists() && !rex_sql_table::get(rex::getTable(rex::getTempPrefix() . 'search_it_cache'))->exists()) {
    rex_sql_table::get(rex::getTable('search_it_cache'))->setName(rex::getTable(rex::getTempPrefix() . 'search_it_cache'))->alter();
}
if (rex_sql_table::get(rex::getTable('search_it_index'))->exists() && !rex_sql_table::get(rex::getTable(rex::getTempPrefix() . 'search_it_index'))->exists()) {
    rex_sql_table::get(rex::getTable('search_it_index'))->setName(rex::getTable(rex::getTempPrefix() . 'search_it_index'))->alter();
}
if (rex_sql_table::get(rex::getTable('search_it_keywords'))->exists() && !rex_sql_table::get(rex::getTable(rex::getTempPrefix() . 'search_it_keywords'))->exists()) {
    rex_sql_table::get(rex::getTable('search_it_keywords'))->setName(rex::getTable(rex::getTempPrefix() . 'search_it_keywords'))->alter();
}

\rex_sql_table::get(
    \rex::getTable(rex::getTempPrefix() . 'search_it_index'))
    ->ensureColumn(new \rex_sql_column('lastindexed', 'VARCHAR(255)', TRUE))
    ->alter();

$addon->includeFile(__DIR__ . '/install.php');
