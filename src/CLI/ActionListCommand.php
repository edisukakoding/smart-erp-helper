<?php

namespace Esikat\Helper\CLI;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ActionListCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('route:actions')
            ->setDescription('Menampilkan daftar aksi di dalam modul')
            ->setHelp('Command ini akan menampilkan daftar aksi yang ada di dalam modul');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $projectRoot    = getcwd();
        $configPath     = $projectRoot . '/src/config/actions.php';

        if(!file_exists($configPath)) {
            $output->writeln("File src/config/actions.php tidak ditemukan!");
        } else {
            $actions    = include $configPath;
            $actions    = array_map(fn($key, $value) => [$key, URLEncrypt($key), $value], array_keys($actions), $actions);
            $table      = new Table($output);
            $table->setHeaders(['Kode', 'Enkripsi', 'Sumber'])->setRows($actions);
            $table->render();
        }

        return Command::SUCCESS;
    }
}