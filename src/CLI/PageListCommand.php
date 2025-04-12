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
            ->setDescription('Menampilkan daftar halaman di dalam modul')
            ->setHelp('Command ini akan menampilkan daftar halaman yang ada di dalam modul');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $projectRoot    = getcwd();
        $configPath     = $projectRoot . '/src/config/pages.php';

        if (!file_exists($configPath)) {
            $output->writeln("File src/config/pages.php tidak ditemukan!");
        } else {
            $pages = include $configPath;

            if (!is_array($pages)) {
                $output->writeln("Format file pages.php tidak valid!");
                return Command::FAILURE;
            }

            // Format baris: [param, enkripsi, sumber, judul]
            $rows = array_map(function ($page) {
                return [
                    $page['param'] ?? '',
                    URLEncrypt($page['param'] ?? ''),
                    $page['sumber'] ?? '',
                    $page['judul'] ?? '',
                ];
            }, $pages);

            $table = new Table($output);
            $table
                ->setHeaders(['Param', 'Enkripsi', 'Sumber', 'Judul'])
                ->setRows($rows);
            $table->render();
        }

        return Command::SUCCESS;
    }
}
