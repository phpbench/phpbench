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

use PhpBench\Benchmark\Runner;
use PhpBench\PhpBench;
use PhpBench\Progress\LoggerInterface;
use PhpBench\Progress\LoggerRegistry;
use PhpBench\Report\ReportManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RunCommand extends BaseReportCommand
{
    private $reportManager;
    private $loggerRegistry;
    private $progressLoggerName;
    private $benchPath;
    private $configPath;
    private $runner;

    public function __construct(
        Runner $runner,
        ReportManager $reportManager,
        LoggerRegistry $loggerRegistry,
        $progressLoggerName = null,
        $benchPath = null,
        $configPath = null
    ) {
        parent::__construct();
        $this->reportManager = $reportManager;
        $this->loggerRegistry = $loggerRegistry;
        $this->progressLoggerName = $progressLoggerName;
        $this->benchPath = $benchPath;
        $this->configPath = $configPath;
        $this->runner = $runner;
    }

    public function configure()
    {
        parent::configure();
        $this->setName('run');
        $this->setDescription('Run benchmarks');
        $this->setHelp(<<<EOT
Run benchmark files at given <comment>path</comment>

    $ %command.full_name% /path/to/bench

All bench marks under the given path will be executed recursively.
EOT
        );
        $this->addArgument('path', InputArgument::OPTIONAL, 'Path to benchmark(s)');
        $this->addOption('filter', array(), InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Ignore all benchmarks not matching this filter (can be a regex)');
        $this->addOption('group', array(), InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Group to run (can be specified multiple times)');
        $this->addOption('dump-file', 'd', InputOption::VALUE_OPTIONAL, 'Dump XML result to named file');
        $this->addOption('dump', null, InputOption::VALUE_NONE, 'Dump XML result to stdout and suppress all other output');
        $this->addOption('parameters', null, InputOption::VALUE_REQUIRED, 'Override parameters to use in (all) benchmarks');
        $this->addOption('iterations', null, InputOption::VALUE_REQUIRED, 'Override number of iteratios to run in (all) benchmarks');
        $this->addOption('revs', null, InputOption::VALUE_REQUIRED, 'Override number of revs (revolutions) on (all) benchmarks');
        $this->addOption('progress', 'l', InputOption::VALUE_REQUIRED, 'Progress logger to use, one of <comment>dots</comment>, <comment>classdots</comment>');
        $this->addOption('retry-threshold', 'r', InputOption::VALUE_REQUIRED, 'Set target allowable deviation', null);

        // this option is parsed before the container is compiled.
        $this->addOption('bootstrap', 'b', InputOption::VALUE_REQUIRED, 'Set or override the bootstrap file.');
        $this->addOption('sleep', null, InputOption::VALUE_REQUIRED, 'Number of microseconds to sleep between iterations');
        $this->addOption('context', null, InputOption::VALUE_REQUIRED, 'Context label to apply to the suite result (useful when comparing reports)');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $consoleOutput = $output;

        $reports = $input->getOption('report');
        $outputs = $input->getOption('output');
        $dump = $input->getOption('dump');
        $parametersJson = $input->getOption('parameters');
        $iterations = $input->getOption('iterations');
        $revs = $input->getOption('revs');
        $configPath = $input->getOption('config');
        $filters = $input->getOption('filter');
        $groups = $input->getOption('group');
        $dumpfile = $input->getOption('dump-file');
        $progressLoggerName = $input->getOption('progress') ?: $this->progressLoggerName;
        $inputPath = $input->getArgument('path');
        $retryThreshold = $input->getOption('retry-threshold');
        $sleep = $input->getOption('sleep');

        $path = $inputPath ?: $this->benchPath;

        $reportNames = $this->reportManager->processCliReports($reports);
        $outputNames = $this->reportManager->processCliOutputs($outputs);

        if (null === $path) {
            throw new \InvalidArgumentException(
                'You must either specify or configure a path where your benchmarks can be found.'
            );
        }

        $contextName = $input->getOption('context');

        $parameters = array();
        if ($parametersJson) {
            $parameters = json_decode($parametersJson, true);
            if (null === $parameters) {
                throw new \InvalidArgumentException(sprintf(
                    'Could not decode parameters JSON string: "%s"', $parametersJson
                ));
            }
        }

        $progressLogger = $this->loggerRegistry->getProgressLogger($progressLoggerName);
        $progressLogger->setOutput($consoleOutput);

        $suiteResult = $this->executeBenchmarks($contextName, $path, $filters, $groups, $parameters, $iterations, $revs, $configPath, $retryThreshold, $sleep, $progressLogger);
        if ($dumpfile) {
            $xml = $suiteResult->dump();
            file_put_contents($dumpfile, $xml);
            $consoleOutput->writeln('Dumped result to ' . $dumpfile);
        }

        if ($dump) {
            $xml = $suiteResult->dump();
            $output->write($xml);
        } elseif ($reportNames) {
            $this->reportManager->renderReports($output, $suiteResult, $reportNames, $outputNames);
        }
    }

    private function executeBenchmarks(
        $contextName,
        $path,
        array $filters,
        array $groups,
        $parameters,
        $iterations,
        $revs,
        $configPath,
        $retryThreshold,
        $sleep,
        LoggerInterface $progressLogger = null
    ) {
        if ($progressLogger) {
            $this->runner->setProgressLogger($progressLogger);
        }

        if ($configPath) {
            $this->runner->setConfigPath($configPath);
        }

        if ($iterations) {
            $this->runner->overrideIterations($iterations);
        }

        if ($revs) {
            $this->runner->overrideRevs($revs);
        }

        if ($filters) {
            $this->runner->setFilters($filters);
        }

        if ($parameters) {
            $this->runner->overrideParameters($parameters);
        }

        if (null !== $sleep) {
            $this->runner->overrideSleep($sleep);
        }

        if ($groups) {
            $this->runner->setGroups($groups);
        }

        if ($retryThreshold) {
            $this->runner->setRetryThreshold($retryThreshold);
        }

        return $this->runner->runAll($contextName, $path);
    }
}
