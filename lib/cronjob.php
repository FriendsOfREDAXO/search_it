<?php

class rex_cronjob_reindex extends rex_cronjob
{
    public function execute()
    {

        if ( rex_addon::get('search_it')->isAvailable() ) {

            //$message = $this->getParam('action').':'."\n";

            $search_it = new search_it();
            $includeColumns = is_array(rex_addon::get('search_it')->getConfig('include')) ? rex_addon::get('search_it')->getConfig('include') : [];
            switch ($this->getParam('action')){
                case 2:
                    // Spalten neu indexieren
                    foreach( $includeColumns as $table => $columnArray ){
                        foreach( $columnArray as $column ){
                            $search_it->indexColumn($table, $column);
                        }
                    }
                    break;

                case 3:
                    // Artikel neu indexieren
                    $art_sql = rex_sql::factory();
                    $art_sql->setTable(rex::getTable('article'));
                    if( $art_sql->select('id,clang_id') ){
                        foreach( $art_sql->getArray() as $art ){
                            $search_it->indexArticle($art['id'], $art['clang_id']);
                        }
                    }
                    break;

                case 1:
                default:
                    $search_it->generateIndex();
                    break;
            }

            //if ( $message != '' ) { $this->setMessage($message); }
            return true;
        }
        $this->setMessage('Search it is not installed');
        return false;
    }
    public function getTypeName()
    {
        return rex_i18n::msg('search_it_reindex');
    }
    public function getParamFields()
    {
        $fields = [
            [
                'label' => rex_i18n::msg('search_it_generate_actions_title'),
                'name' => 'action',
                'type' => 'select',
                'options' => [
                    1 => rex_i18n::msg('search_it_generate_full'),
                    2 => rex_i18n::msg('search_it_generate_columns'),
                    3 => rex_i18n::msg('search_it_generate_articles')],
                'default' => '1',
                'notice' => rex_i18n::msg('search_it_generate_actions_title'),
            ],
        ];
        return $fields;
    }
}