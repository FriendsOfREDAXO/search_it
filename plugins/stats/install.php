<?php

rex_sql_table::get(rex::getTablePrefix().'search_it_stats_searchterms')
    ->ensureColumn(new rex_sql_column('id', 'int(11)', false, null, 'auto_increment'))
    ->ensureColumn(new rex_sql_column('term', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('time', 'datetime'))
    ->ensureColumn(new rex_sql_column('resultcount', 'int(11)', false, '0'))
    ->setPrimaryKey('id')
    ->ensure();
