<?php

namespace PhpBench\Console\Command;

use PhpBench\Development\ConfigSchemaDumper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ConfigSchemaDumpCommand extends Command
{
    public function __construct(private readonly ConfigSchemaDumper $schemaDumper, private readonly OutputInterface $stdOut)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('doc:config-schema');
        $this->setDescription('Dump the JSON schema for the configuration');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->stdOut->write(
            $this->schemaDumper->dump(),
            false,
            OutputInterface::OUTPUT_RAW
        );

        return 0;
    }
}
