<?php

namespace FriendsOfRedaxo\SearchIt\Console;

use FriendsOfRedaxo\SearchIt\SearchIt;
use rex_console_command;
use rex_i18n;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearCacheCommand extends rex_console_command
{
    protected function configure()
    {
        $this->setDescription(rex_i18n::rawMsg('search_it_generate_delete_cache'));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = $this->getStyle($input, $output);
        $io->title('search_it clear cache');

        $search_it = new SearchIt();
        $global_return = $search_it->deleteCache();

        if ($global_return < 4) {
            echo $io->success(rex_i18n::rawMsg('search_it_generate_cache_deleted'));
            return 0;
        } else {
            echo $io->error(rex_i18n::rawMsg('search_it_generate_cache_deleted_error'));
        }

        return 1;
    }
}
