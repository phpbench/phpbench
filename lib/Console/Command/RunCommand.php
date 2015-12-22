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

class RunCommand extends BaseReportCommand
{
    private $reportManager;
    private $loggerRegistry;
    private $progressLoggerName;
    private $benchPath;
    private $configPath;
    private $runner;
    private $timeUnit;

    public function __construct(
        Runner $runner,
        ReportManager $reportManager,
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
        $this->addOption('time-unit', null, InputOption::VALUE_REQUIRED, 'Override the time unit');
        $this->addOption('mode', null, InputOption::VALUE_REQUIRED, 'Override the unit display mode ("throughput", "time")');
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
        $progressLoggerName = $input->getOption('progress') ?: $this->progressLoggerName;
        $dump = $input->getOption('dump');
        $dumpfile = $input->getOption('dump-file');
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
                'iterations' => $input->getOption('iterations'),
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
