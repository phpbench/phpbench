<?php

namespace PhpBench\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PhpBench\Result\Loader\XmlLoader;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ReportCommand extends BaseCommand
{
    public function configure()
    {
        $this->setName('report');
        $this->setDescription('Generate a report from an XML file');
        $this->setHelp(<<<EOT
Generate a report from an existing XML file.

To dump an XML file, use the <info>run</info> command with the
<comment>dumpfile</comment option.
EOT
        );

        $this->addArgument('file', InputArgument::REQUIRED, 'XML file');
        $this->addOption('report', null, InputOption::VALUE_REQUIRED|InputOption::VALUE_IS_ARRAY, 'Report(s) to generate');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $reportConfigs = $this->normalizeReportConfig($input->getOption('report'));
        $file = $input->getArgument('file');

        if (!$reportConfigs) {
            throw new \InvalidArgumentException(
                'You must specify at least one report, e.g.: --report=console_table'
            );
        }

        if (!file_exists($file)) {
            throw new \InvalidArgumentException(sprintf(
                'Could not find file "%s"', $file
            ));
        }

        $loader = new XmlLoader();
        $suiteResult = $loader->load(file_get_contents($file));
        $this->generateReports($output, $suiteResult, $reportConfigs);
    }
}
