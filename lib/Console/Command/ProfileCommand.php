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
use PhpBench\Benchmark\RunnerContext;
use PhpBench\PhpBench;
use PhpBench\Progress\LoggerRegistry;
use PhpBench\Report\ReportManager;
use PhpBench\Util\TimeUnit;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ProfileCommand extends Command
{
    private $loggerRegistry;
    private $progressLoggerName;
    private $benchPath;
    private $configPath;
    private $runner;
    private $timeUnit;

    public function __construct(
        Runner $runner,
        LoggerRegistry $loggerRegistry,
        TimeUnit $timeUnit,
        $progressLoggerName = null,
        $benchPath = null,
        $configPath = null
    ) {
        parent::__construct();
        $this->reportManager = $reportManager;
        $this->loggerRegistry = $loggerRegistry;
        $this->timeUnit = $timeUnit;
        $this->progressLoggerName = $progressLoggerName;
        $this->benchPath = $benchPath;
        $this->configPath = $configPath;
        $this->runner = $runner;
    }

    public function configure()
    {
        Configure\Report::configure($this);
        Configure\Executor::configure($this);

        $this->setName('run');
        $this->setDescription('Run benchmarks');
        $this->setHelp(<<<EOT
Profile benchmark files at given <comment>path</comment>

    $ %command.full_name% /path/to/bench

All bench marks under the given path will be executed recursively using the
named profiler (XDebug by default).
EOT
        );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $consoleOutput = $output;
        $progressLoggerName = $input->getOption('progress') ?: $this->progressLoggerName;
        $timeUnit = $input->getOption('time-unit');
        $mode = $input->getOption('mode');

        if ($timeUnit) {
            $this->timeUnit->overrideDestUnit($timeUnit);
        }

        if ($mode) {
            $this->timeUnit->overrideMode($mode);
        }

        $context = new RunnerContext(
            $input->getArgument('path') ?: $this->benchPath,
            array(
                'context_name' => $input->getOption('context'),
                'parameters' => $this->getParameters($input->getOption('parameters')),
                'iterations' => 1,
                'revolutions' => $input->getOption('revs'),
                'filters' => $input->getOption('filter'),
                'groups' => $input->getOption('group'),
                'retry_threshold' => $input->getOption('retry-threshold'),
                'sleep' => $input->getOption('sleep'),
            )
        );

        $reportNames = $this->reportManager->processCliReports($reports);
        $outputNames = $this->reportManager->processCliOutputs($outputs);

        // TODO: move setOutput to logger registry?
        $progressLogger = $this->loggerRegistry->getProgressLogger($progressLoggerName);
        $progressLogger->setOutput($consoleOutput);
        $this->runner->setProgressLogger($progressLogger);

        $suiteResult = $this->runner->run($context);

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

        if ($suiteResult->hasErrors()) {
            return 1;
        }

        return 0;
    }

    private function getParameters($parametersJson)
    {
        if (null === $parametersJson) {
            return;
        }

        $parameters = array();
        if ($parametersJson) {
            $parameters = json_decode($parametersJson, true);
            if (null === $parameters) {
                throw new \InvalidArgumentException(sprintf(
                    'Could not decode parameters JSON string: "%s"', $parametersJson
                ));
            }
        }

        return $parameters;
    }
}
