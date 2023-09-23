<?php

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package redaxo\be-style
 *
 * @author bloep
 *
 * @internal
 */
class rex_search_it_command_reindex extends rex_console_command
{
    protected function configure()
    {
        $this->setDescription('Does a complete reindex');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = $this->getStyle($input, $output);
        $io->title('search_it reindex');

        $search_it = new search_it();
        $global_return = $search_it->generateIndex();

        if ($global_return < 4) {
            echo $io->success(rex_i18n::rawMsg('search_it_generate_done'));
            return 0;
        } else {
            echo $io->error(rex_i18n::rawMsg('search_it_generate_error'));
        }

        return 1;
    }
}
