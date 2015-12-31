<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Console\Command\Handler;

use PhpBench\Benchmark\SuiteDocument;
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
        $command->addOption('output', 'o', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Specify output', array('console'));
    }

    public function reportsFromInput(InputInterface $input, OutputInterface $output, SuiteDocument $suiteDocument)
    {
        $reports = $input->getOption('report');
        $outputs = $input->getOption('output');
        $reportNames = $this->processCliReports($reports);
        $outputNames = $this->processCliOutputs($outputs);
        $this->reportManager->renderReports($output, $suiteDocument, $reportNames, $outputNames);
    }

    /**
     * Process - decode and add - the raw CLI reports.
     *
     * @see self::processRawCliConfigs
     *
     * @param array $rawConfigs
     *
     * @return string[]
     */
    private function processCliReports($rawConfigs)
    {
        list($configNames, $configs) = $this->processRawCliConfigs($rawConfigs);
        foreach ($configs as $configName => $config) {
            $this->reportManager->addReport($configName, $config);
        }

        return $configNames;
    }

    /**
     * Process - decode and add - the raw CLI outputs.
     *
     * @see self::processRawCliConfigs
     *
     * @param array $rawConfigs
     *
     * @return string[]
     */
    public function processCliOutputs(array $rawConfigs)
    {
        list($configNames, $configs) = $this->processRawCliConfigs($rawConfigs);
        foreach ($configs as $configName => $config) {
            $this->reportManager->addOutput($configName, $config);
        }

        return $configNames;
    }

    /**
     * Process raw configuration as recieved from the CLI, for example:.
     *
     * ````
     * {"generator": "table", "sort": ["time"]}
     * ````
     *
     * Or simply the name of a pre-configured report to use:
     *
     * ````
     * table
     * ````
     * Accepts an array of strings and returns the names of all reports that
     * have been identified / processed.n
     *
     * Report configurations will be added to the report manager with a generated UUID.
     *
     * @param array $rawConfigs
     *
     * @return array
     */
    private function processRawCliConfigs(array $rawConfigs)
    {
        $configNames = array();
        $configs = array();
        foreach ($rawConfigs as $rawConfig) {
            // If it doesn't look like a JSON string, assume it is the name of a config
            if (substr($rawConfig, 0, 1) !== '{') {
                $configNames[] = $rawConfig;
                continue;
            }

            $config = json_decode($rawConfig, true);

            if (null === $config) {
                throw new \InvalidArgumentException(sprintf(
                    'Could not decode JSON string: %s', $rawConfig
                ));
            }

            $configName = uniqid();
            $configNames[] = $configName;

            $configs[$configName] = $config;
        }

        return array($configNames, $configs);
    }
}
