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
class rex_search_it_command_clearcache extends rex_console_command
{
    protected function configure()
    {
        $this->setDescription(rex_i18n::rawMsg('search_it_generate_delete_cache'));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = $this->getStyle($input, $output);
        $io->title('search_it clear cache');

        $search_it = new search_it();
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
