<?php

namespace PhpBench\Console\Command;

use PhpBench\Development\OptionDumper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ServiceOptionReferenceCommand extends Command
{
    /**
     * @var OutputInterface
     */
    private $stderr;

    /**
     * @var OptionDumper
     */
    private $dumper;

    public function __construct(OptionDumper $dumper, OutputInterface $stderr)
    {
        parent::__construct();
        $this->stderr = $stderr;
        $this->dumper = $dumper;
    }

    protected function configure(): void
    {
        $this->setName('doc:service-options');
        $this->addArgument('path', InputArgument::REQUIRED, 'Output path for generated files');
        $this->addArgument('type', InputArgument::REQUIRED, 'Type of service to generate options for');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $string = $input->getArgument('type');
        $basePath = $input->getArgument('path');
        assert(is_string($string));
        assert(is_string($basePath));

        if (!file_exists($basePath)) {
            mkdir($basePath, 0777, true);
        }

        foreach ($this->dumper->dump($string) as $type => $reference) {
            $path = sprintf('%s/_%s.rst', $basePath, $type);
            file_put_contents($path, $reference);
            $this->stderr->writeln(sprintf('Written: %s', $path));
        }

        return 0;
    }
}
