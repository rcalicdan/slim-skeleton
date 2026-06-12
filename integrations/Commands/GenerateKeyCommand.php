<?php

declare(strict_types=1);

namespace Integrations\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateKeyCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('key:generate')
            ->setDescription('Set the application encryption key')
            ->addOption('show', null, InputOption::VALUE_NONE, 'Display the key instead of modifying the .env file')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force the operation to run even if an APP_KEY is already set')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $key = 'base64:' . base64_encode(random_bytes(32));

        if ($input->getOption('show')) {
            $io->info("Application key: {$key}");

            return Command::SUCCESS;
        }

        $envPath = getcwd() . '/.env';

        if (! file_exists($envPath)) {
            $io->error('The .env file does not exist. Please copy .env.example to .env first.');

            return Command::FAILURE;
        }

        $envContent = file_get_contents($envPath);
        if ($envContent === false) {
            $io->error('Failed to read the .env file.');

            return Command::FAILURE;
        }

        $pattern = "/^APP_KEY=(.*)$/m";

        if (preg_match($pattern, $envContent, $matches)) {
            $existingKey = trim($matches[1]);

            if ($existingKey !== '' && ! $input->getOption('force')) {
                $io->warning('APP_KEY is already set. Use the --force (-f) flag to overwrite it.');

                return Command::FAILURE;
            }

            $envContent = preg_replace($pattern, 'APP_KEY=' . $key, $envContent);
        } else {
            $envContent .= "\nAPP_KEY=" . $key . "\n";
        }

        if (file_put_contents($envPath, $envContent) === false) {
            $io->error('Failed to write the new APP_KEY to the .env file.');

            return Command::FAILURE;
        }

        $io->success("Application key [{$key}] set successfully.");

        return Command::SUCCESS;
    }
}