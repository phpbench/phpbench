<?php

namespace PhpBench\Console\Command;

use Phar;
use PhpBench\Config\ConfigManipulator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ConfigInitCommand extends Command
{
    public function __construct(private ConfigManipulator $initializer)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('config:initialize');
        $this->setDescription('Initialize the configuration file or update the location of the JSON schema');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<comment>// This command creates (or updates) the config ensuring the JSON $schema is set</comment>.');

        if (class_exists(Phar::class) && Phar::running() !== '') {
            $output->writeln(
                <<<EOT
                This command is not supported in the Phar environment as the
                schema file cannot be directly referenced from the JSON file.
                EOT
            );

            return 1;
        }

        $created = !file_exists($this->initializer->configPath());
        $this->initializer->initialize();

        if ($created) {
            $output->writeln(sprintf('Created %s', $this->initializer->configPath()));

            return 0;
        }

        $output->writeln(sprintf('<info>Updated:</> %s', $this->initializer->configPath()));

        return 0;
    }
}
