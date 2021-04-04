<?php

namespace PhpBench\Console\Command;

use PhpBench\Development\ConfigDumper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigReferenceCommand extends Command
{
    /**
     * @var ConfigDumper
     */
    private $dumper;

    public function __construct(ConfigDumper $dumper)
    {
        parent::__construct();
        $this->dumper = $dumper;
    }

    protected function configure(): void
    {
        $this->setName('config:dump-reference');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln($this->dumper->dump(), OutputInterface::OUTPUT_RAW);
        return 0;
    }

}
