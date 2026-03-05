<?php

namespace FriendsOfRedaxo\SearchIt\Console;

use FriendsOfRedaxo\SearchIt\SearchIt;
use rex_console_command;
use rex_i18n;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearCacheCommand extends rex_console_command
{
    protected function configure(): void
    {
        $this->setDescription(rex_i18n::rawMsg('search_it_generate_delete_cache'));
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = $this->getStyle($input, $output);
        $io->title('search_it clear cache');

        $search_it = new SearchIt();
        $search_it->deleteCache();

        $io->success(rex_i18n::rawMsg('search_it_generate_cache_deleted'));
        return 0;
    }
}
