<?php

namespace FriendsOfRedaxo\SearchIt\Cronjob;

use FriendsOfRedaxo\SearchIt\SearchIt;
use rex_addon;
use rex_cronjob;
use rex_i18n;

class ClearCache extends rex_cronjob
{
    public function execute(): bool
    {
        if (rex_addon::get('search_it')->isAvailable()) {
            $search_it = new SearchIt();
            $search_it->deleteCache();
            return true;
        }

        $this->setMessage('Search it is not installed');
        return false;
    }

    public function getTypeName(): string
    {
        return rex_i18n::msg('search_it_generate_delete_cache');
    }

    public function getParamFields(): array
    {
        return [];
    }
}
