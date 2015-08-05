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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use PhpBench\Benchmark\Runner;
use PhpBench\Result\Dumper\XmlDumper;
use Symfony\Component\Console\Output\NullOutput;
use PhpBench\ProgressLogger;
use PhpBench\PhpBench;
use PhpBench\Report\ReportManager;
use PhpBench\ProgressLoggerRegistry;

class RunCommand extends Command
{
    private $xmlDumper;
    private $reportManager;
    private $loggerRegistry;
    private $progressLoggerName;
    private $benchPath;
    private $enableGc;
    private $configPath;
    private $runner;

    public function __construct(
        Runner $runner,
        XmlDumper $xmlDumper,
        ReportManager $reportManager,
        ProgressLoggerRegistry $loggerRegistry,
        $progressLoggerName = null,
        $benchPath = null,
        $enableGc = null,
        $configPath = null
    ) {
        parent::__construct();
        $this->xmlDumper = $xmlDumper;
        $this->reportManager = $reportManager;
        $this->loggerRegistry = $loggerRegistry;
        $this->progressLoggerName = $progressLoggerName;
        $this->benchPath = $benchPath;
        $this->enableGc = $enableGc;
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
        $this->addOption('process-isolation', 'pi', InputOption::VALUE_REQUIRED, 'Override rocess isolation policy, one of <comment>iteration</comment>, <comment>iterations</comment> or <comment>none</comment>');
        $this->addOption('no-setup', null, InputOption::VALUE_NONE, 'Do not execute setUp or tearDown methods');
        $this->addOption('progress', 'l', InputOption::VALUE_REQUIRED, 'Progress logger to use, one of <comment>dots</comment>, <comment>benchdots</comment>', 'dots');
        $this->addOption('gc-enable', null, InputOption::VALUE_NONE, 'Enable garbage collection');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $consoleOutput = $output;

        $reports = $input->getOption('report');
        $dump = $input->getOption('dump');
        $parametersJson = $input->getOption('parameters');
        $noSetup = $input->getOption('no-setup');
        $iterations = $input->getOption('iterations');
        $revs = $input->getOption('revs');
        $configPath = $input->getOption('config');
        $enableGc = $input->getOption('gc-enable');
        $enableGc = null !== $enableGc ?: $this->enableGc;
        $processIsolation = $input->getOption('process-isolation') ?: null;
        $subjects = $input->getOption('subject');
        $groups = $input->getOption('group');
        $dumpfile = $input->getOption('dump-file');
        $progressLoggerName = $input->getOption('progress') ?: $this->progressLoggerName;
        $path = $input->getArgument('path') ?: $this->benchPath;

        $processIsolation = $processIsolation === 'none' ? false : $processIsolation;
        Runner::validateProcessIsolation($processIsolation);

        $reportNames = $this->reportManager->processCliReports($reports);

        if (false === $enableGc) {
            gc_disable();
        }

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
        $suiteResult = $this->executeBenchmarks($path, $subjects, $groups, $noSetup, $parameters, $iterations, $revs, $processIsolation, $configPath, $progressLogger);
        $consoleOutput->writeln('');

        $consoleOutput->writeln(sprintf(
            '<greenbg>Done (%s subjects, %s iterations) in %ss</greenbg>',
            count($suiteResult->getSubjectResults()),
            count($suiteResult->getIterationResults()),
            number_format(microtime(true) - $startTime, 2)
        ));

        if ($dumpfile) {
            $xml = $this->xmlDumper->dump($suiteResult)->saveXml();
            file_put_contents($dumpfile, $xml);
            $consoleOutput->writeln('Dumped result to ' . $dumpfile);
        }

        $consoleOutput->writeln('');

        if ($dump) {
            $xml = $this->xmlDumper->dump($suiteResult)->saveXml();
            $output->write($xml);
        } elseif ($reportNames) {
            $this->reportManager->generateReports($consoleOutput, $suiteResult, $reportNames);
        }
    }

    private function executeBenchmarks(
        $path,
        array $subjects,
        array $groups,
        $noSetup,
        $parameters,
        $iterations,
        $revs,
        $processIsolation,
        $configPath,
        ProgressLogger $progressLogger = null
    ) {
        if ($progressLogger) {
            $this->runner->setProgressLogger($progressLogger);
        }

        $this->runner->setProcessIsolation($processIsolation);

        if ($configPath) {
            $this->runner->setConfigPath($configPath);
        }

        if ($noSetup) {
            $this->runner->disableSetup();
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
