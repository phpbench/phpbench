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
    /**
     * @var ReportManager
     */
    private $reportManager;

    /**
     * @var StorageRegistry
     */
    private $storageRegistry;

    /**
     * @var SuiteCollectionHandler
     */
    private $suiteCollectionHandler;

    public function __construct(ReportManager $reportManager, SuiteCollectionHandler $suiteCollectionHandler)
    {
        $this->reportManager = $reportManager;
        $this->suiteCollectionHandler = $suiteCollectionHandler;
    }

    public static function configure(Command $command)
    {
        $command->addOption('report', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Report name or configuration in JSON format');
        $command->addOption('output', 'o', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Specify output', ['console']);
        SuiteCollectionHandler::configure($command);
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
        if ($input->getOption('uuid')) {
            $baselineCollection = $this->suiteCollectionHandler->suiteCollectionFromInput($input);
            $collection->mergeCollection($baselineCollection);
        }

        $this->reportManager->renderReports($output, $collection, $reports, $outputs);
    }
}
