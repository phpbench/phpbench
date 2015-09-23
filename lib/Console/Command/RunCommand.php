<?php

/*
 * This file is part of the PHP Bench package
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
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

class RunCommand extends Command
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
        $this->setName('run');
        $this->setDescription('Run benchmarks');
        $this->setHelp(<<<EOT
Run benchmark files at given <comment>path</comment>

    $ %command.full_name% /path/to/bench

All bench marks under the given path will be executed recursively.
EOT
        );
        $this->addArgument('path', InputArgument::OPTIONAL, 'Path to benchmark(s)');
        $this->addOption('report', array(), InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Report name or configuration in JSON format');
        $this->addOption('subject', array(), InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Subject to run (can be specified multiple times)');
        $this->addOption('group', array(), InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Group to run (can be specified multiple times)');
        $this->addOption('dump-file', 'df', InputOption::VALUE_OPTIONAL, 'Dump XML result to named file');
        $this->addOption('dump', null, InputOption::VALUE_NONE, 'Dump XML result to stdout and suppress all other output');
        $this->addOption('parameters', null, InputOption::VALUE_REQUIRED, 'Override parameters to use in (all) benchmarks');
        $this->addOption('iterations', null, InputOption::VALUE_REQUIRED, 'Override number of iteratios to run in (all) benchmarks');
        $this->addOption('revs', null, InputOption::VALUE_REQUIRED, 'Override number of revs (revolutions) on (all) benchmarks');
        $this->addOption('progress', 'l', InputOption::VALUE_REQUIRED, 'Progress logger to use, one of <comment>dots</comment>, <comment>classdots</comment>', 'dots');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $consoleOutput = $output;

        $reports = $input->getOption('report');
        $dump = $input->getOption('dump');
        $parametersJson = $input->getOption('parameters');
        $iterations = $input->getOption('iterations');
        $revs = $input->getOption('revs');
        $configPath = $input->getOption('config');
        $subjects = $input->getOption('subject');
        $groups = $input->getOption('group');
        $dumpfile = $input->getOption('dump-file');
        $progressLoggerName = $input->getOption('progress') ?: $this->progressLoggerName;
        $path = $input->getArgument('path') ?: $this->benchPath;

        $reportNames = $this->reportManager->processCliReports($reports);

        if (null === $path) {
            throw new \InvalidArgumentException(
                'You must either specify or configure a path where your benchmarks can be found.'
            );
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

        if ($dump) {
            $consoleOutput = new NullOutput();
        }

        $consoleOutput->writeln('PhpBench ' . PhpBench::VERSION . '. Running benchmarks.');

        if ($this->configPath) {
            $consoleOutput->writeln(sprintf('Using configuration file: %s', $this->configPath));
        }

        $progressLogger = $this->loggerRegistry->getProgressLogger($progressLoggerName);
        $progressLogger->setOutput($consoleOutput);

        $consoleOutput->writeln('');
        $startTime = microtime(true);
        $suiteResult = $this->executeBenchmarks($path, $subjects, $groups, $parameters, $iterations, $revs, $configPath, $progressLogger);
        $consoleOutput->writeln('');

        $consoleOutput->writeln(sprintf(
            '<greenbg>Done (%s subjects, %s iterations) in %ss</greenbg>',
            $suiteResult->getNbSubjects(),
            $suiteResult->getNbIterations(),
            number_format(microtime(true) - $startTime, 2)
        ));

        if ($dumpfile) {
            $xml = $suiteResult->saveXml();
            file_put_contents($dumpfile, $xml);
            $consoleOutput->writeln('Dumped result to ' . $dumpfile);
        }

        $consoleOutput->writeln('');

        if ($dump) {
            $xml = $suiteResult->saveXml();
            $output->write($xml);
        } elseif ($reportNames) {
            $this->reportManager->generateReports($consoleOutput, $suiteResult, $reportNames);
        }
    }

    private function executeBenchmarks(
        $path,
        array $subjects,
        array $groups,
        $parameters,
        $iterations,
        $revs,
        $configPath,
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

        if ($subjects) {
            $this->runner->overrideSubjects($subjects);
        }

        if ($parameters) {
            $this->runner->overrideParameters($parameters);
        }

        if ($groups) {
            $this->runner->setGroups($groups);
        }

        return $this->runner->runAll($path);
    }
}
