<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace PhpBench\Console\Command\Handler;

use PhpBench\Model\SuiteCollection;
use PhpBench\Report\ReportManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ReportHandler
{
    private $reportManager;

    public function __construct(ReportManager $reportManager)
    {
        $this->reportManager = $reportManager;
    }

    public static function configure(Command $command)
    {
        $command->addOption('report', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Report name or configuration in JSON format');
        $command->addOption('output', 'o', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Specify output', ['console']);
    }

    public function validateReportsFromInput(InputInterface $input)
    {
        $reportNames = $input->getOption('report');
        $this->reportManager->validateReportNames($reportNames);
    }

    public function reportsFromInput(InputInterface $input, OutputInterface $output, SuiteCollection $collection)
    {
        $reports = $input->getOption('report');
        $outputs = $input->getOption('output');
        $this->reportManager->renderReports($output, $collection, $reports, $outputs);
    }
}
