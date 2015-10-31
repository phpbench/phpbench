<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Console\Command;

use PhpBench\Benchmark\SuiteDocument;
use PhpBench\Report\ReportManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReportCommand extends BaseReportCommand
{
    private $reportManager;

    public function __construct(
        ReportManager $reportManager
    ) {
        parent::__construct();
        $this->reportManager = $reportManager;
    }

    public function configure()
    {
        parent::configure();
        $this->setName('report');
        $this->setDescription('Generate a report from an XML file');
        $this->setHelp(<<<EOT
Generate a report from an existing XML file.

To dump an XML file, use the <info>run</info> command with the
<comment>dump-file</comment option.
EOT
        );

        $this->addArgument('file', InputArgument::REQUIRED, 'XML file');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $reports = $input->getOption('report');
        $reportNames = $this->reportManager->processCliReports($reports);
        $file = $input->getArgument('file');
        $outputs = $input->getOption('output');
        $outputNames = $this->reportManager->processCliOutputs($outputs);

        if (!$reportNames) {
            throw new \InvalidArgumentException(
                'You must specify or configure at least one report, e.g.: --report=default'
            );
        }

        if (!file_exists($file)) {
            throw new \InvalidArgumentException(sprintf(
                'Could not find suite result file "%s" (cwd: %s)', $file, getcwd()
            ));
        }

        $suiteResult = new SuiteDocument();
        $suiteResult->loadXml(file_get_contents($file));
        $this->reportManager->renderReports($output, $suiteResult, $reportNames, $outputNames);
    }
}
