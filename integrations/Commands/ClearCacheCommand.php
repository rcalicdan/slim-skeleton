<?php

declare(strict_types=1);

namespace Integrations\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function Rcalicdan\ConfigLoader\config;

class ClearCacheCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('cache:clear')
            ->setDescription('Flush the PHP-DI and BladeOne template caches')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $bladeCache = config('blade.cache_path');
        $diCache = config('container.settings.cache_path');

        $io->title('Flushing Application Caches');

        if ($bladeCache && is_dir($bladeCache)) {
            $this->deleteDirectoryContents($bladeCache);
            $io->success('BladeOne template cache cleared.');
        }

        if ($diCache && is_dir($diCache)) {
            $this->deleteDirectoryContents($diCache);
            $io->success('PHP-DI container compilation cache cleared.');
        }

        return Command::SUCCESS;
    }

    private function deleteDirectoryContents(string $dir): void
    {
        $files = glob($dir . '/*');
        if ($files === false) {
            return;
        }

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            } elseif (is_dir($file)) {
                $this->deleteDirectoryContents($file);
                rmdir($file);
            }
        }
    }
}