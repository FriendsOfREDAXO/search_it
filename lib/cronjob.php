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

                case 4:
                    // URLs neu indexieren
					if(rex_addon::get('search_it')->getConfig('index_url_addon') && search_it_isUrlAddOnAvailable()) {
						$url_sql = rex_sql::factory();
                        $url_sql->setTable(search_it_getUrlAddOnTableName());
						if ($url_sql->select('url_hash, article_id, clang_id, profile_id, data_id')) {
							// index und cache zuerst löschen, damit keine alten Einträge überleben
							$search_it->deleteIndexForType("url");
							foreach ($url_sql->getArray() as $url) {
								$search_it->indexUrl($url['url_hash'], $url['article_id'], $url['clang_id'], $url['profile_id'], $url['data_id']);
							}
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
                    3 => rex_i18n::msg('search_it_generate_articles'),
                    4 => rex_i18n::msg('search_it_generate_urls')],
                'default' => '1',
                'notice' => rex_i18n::msg('search_it_generate_actions_title'),
            ],
        ];
        return $fields;
    }
}
