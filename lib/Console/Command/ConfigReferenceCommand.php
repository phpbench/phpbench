<?php

namespace PhpBench\Console\Command;

use PhpBench\Development\ConfigDumper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigReferenceCommand extends Command
{
    public function __construct(private readonly ConfigDumper $dumper, private readonly OutputInterface $stdout)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('doc:config-reference');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->stdout->writeln($this->dumper->dump(), OutputInterface::OUTPUT_RAW);

        return 0;
    }
}
