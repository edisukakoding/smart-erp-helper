<?php

namespace Esikat\Helper\CLI;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PageListCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('route:pages')
            ->setDescription('Menampilkan daftar halaman dalam modul')
            ->setHelp('Command ini akan menampilkan daftar halaman yang ada di dalam modul');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $projectRoot    = getcwd();
        $configPath     = $projectRoot . '/src/config/pages.php';

        if(!file_exists($configPath)) {
            $output->writeln("File src/config/pages.php tidak ditemukan!");
        } else {
            $pages = include $configPath;
            $pages = array_map(fn($key, $value) => [$key, URLEncrypt($key), $value], array_keys($pages), $pages);
            $table = new Table($output);
            $table->setHeaders(['Kode', 'Enkripsi', 'Sumber'])->setRows($pages);
            $table->render();
        }

        return Command::SUCCESS;
    }
}