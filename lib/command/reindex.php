<?php

namespace FriendsOfRedaxo\SearchIt\Console;

use FriendsOfRedaxo\SearchIt\SearchIt;
use rex_console_command;
use rex_i18n;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReindexCommand extends rex_console_command
{
    protected function configure(): void
    {
        $this->setDescription('Does a complete reindex');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = $this->getStyle($input, $output);
        $io->title('search_it reindex');

        $search_it = new SearchIt();
        $global_return = $search_it->generateIndex();

        if ($global_return < 4) {
            $io->success(rex_i18n::rawMsg('search_it_generate_done'));
            return 0;
        }

        $io->error(rex_i18n::rawMsg('search_it_generate_error'));
        return 1;
    }
}
