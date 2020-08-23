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
use PhpBench\Storage\StorageRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ReportHandler
{
    public const OPT_REPORT = 'report';
    public const OPT_OUTPUT = 'output';

    /**
     * @var ReportManager
     */
    private $reportManager;

    /**
     * @var StorageRegistry
     */
    private $storageRegistry;

    public function __construct(ReportManager $reportManager)
    {
        $this->reportManager = $reportManager;
    }

    public static function configure(Command $command): void
    {
        $command->addOption(self::OPT_REPORT, null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Report name or configuration in JSON format');
        $command->addOption(self::OPT_OUTPUT, 'o', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Specify output', ['console']);
    }

    public function validateReportsFromInput(InputInterface $input): void
    {
        $reportNames = $input->getOption(self::OPT_REPORT);
        $this->reportManager->validateReportNames($reportNames);
    }

    public function reportsFromInput(InputInterface $input, OutputInterface $output, SuiteCollection $collection): void
    {
        $reports = $input->getOption(self::OPT_REPORT);
        $outputs = $input->getOption(self::OPT_OUTPUT);

        $this->reportManager->renderReports($output, $collection, $reports, $outputs);
    }
}
