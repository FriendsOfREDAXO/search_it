<?php

class rex_cronjob_clearcache extends rex_cronjob
{
    public function execute()
    {

        if (rex_addon::get('search_it')->isAvailable()) {

            //$message = $this->getParam('action').':'."\n";

            $search_it = new search_it();

            $search_it->deleteCache();



            //if ( $message != '' ) { $this->setMessage($message); }
            return true;
        }
        $this->setMessage('Search it is not installed');
        return false;
    }

    public function getTypeName()
    {
        return rex_i18n::msg('search_it_generate_delete_cache');
    }

    public function getParamFields()
    {
        $fields = [];
        return $fields;
    }
}
