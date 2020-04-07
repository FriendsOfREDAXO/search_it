<?php

rex_sql_table::get(rex::getTable(rex::getTempPrefix().'search_it_index'))
    ->ensureColumn(new rex_sql_column('id', 'int(11)', false, null, 'auto_increment'))
    ->ensureColumn(new rex_sql_column('fid', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('catid', 'int(11)', true))
    ->ensureColumn(new rex_sql_column('ftable', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('fcolumn', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('texttype', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('clang', 'int(11)', true))
    ->ensureColumn(new rex_sql_column('filename', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('fileext', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('plaintext', 'longtext'))
    ->ensureColumn(new rex_sql_column('unchangedtext', 'longtext'))
    ->ensureColumn(new rex_sql_column('teaser', 'longtext'))
    ->ensureColumn(new rex_sql_column('values', 'longtext'))
    ->setPrimaryKey('id')
    ->ensureIndex(new rex_sql_index('fid', ['fid']))
    ->ensureIndex(new rex_sql_index('plaintext', ['plaintext'], rex_sql_index::FULLTEXT))
    ->ensureIndex(new rex_sql_index('unchangedtext', ['unchangedtext'], rex_sql_index::FULLTEXT))
    ->ensureIndex(new rex_sql_index('plaintext_2', ['plaintext', 'unchangedtext'], rex_sql_index::FULLTEXT))
    ->ensure();


rex_sql_table::get(rex::getTable(rex::getTempPrefix().'search_it_cache'))
    ->ensureColumn(new rex_sql_column('id', 'int(11)', false, null, 'auto_increment'))
    ->ensureColumn(new rex_sql_column('hash', 'char(32)'))
    ->ensureColumn(new rex_sql_column('returnarray', 'longtext', true))
    ->setPrimaryKey('id')
    ->ensure();

rex_sql_table::get(rex::getTable(rex::getTempPrefix().'search_it_cacheindex_ids'))
    ->ensureColumn(new rex_sql_column('id', 'int(11)', false, null, 'auto_increment'))
    ->ensureColumn(new rex_sql_column('index_id', 'int(11)', true))
    ->ensureColumn(new rex_sql_column('cache_id', 'varchar(255)', true))
    ->setPrimaryKey('id')
    ->ensure();

rex_sql_table::get(rex::getTable(rex::getTempPrefix().'search_it_keywords'))
    ->ensureColumn(new rex_sql_column('id', 'int(11)', false, null, 'auto_increment'))
    ->ensureColumn(new rex_sql_column('keyword', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('soundex', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('metaphone', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('colognephone', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('clang', 'int(11)', false, '-1'))
    ->ensureColumn(new rex_sql_column('count', 'int(11)', false, '1'))
    ->setPrimaryKey('id')
    ->ensureIndex(new rex_sql_index('keyword', ['keyword', 'clang'], rex_sql_index::UNIQUE))
    ->ensure();