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
use Symfony\Component\Finder\Finder;
use PhpBench\Benchmark\CollectionBuilder;
use PhpBench\Benchmark\SubjectBuilder;
use PhpBench\Benchmark\Runner;
use PhpBench\Result\SuiteResult;
use PhpBench\Result\Dumper\XmlDumper;
use Symfony\Component\Console\Output\NullOutput;
use PhpBench\ProgressLogger;
use PhpBench\PhpBench;

class RunCommand extends BaseCommand
{
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
        $reports = $input->getOption('report');

        $consoleOutput = $output;
        $dump = $input->getOption('dump');
        $parametersJson = $input->getOption('parameters');
        $noSetup = $input->getOption('no-setup');
        $iterations = $input->getOption('iterations');
        $revs = $input->getOption('revs');
        $configFile = $input->getOption('config');
        $processIsolation = $input->getOption('process-isolation') ?: null;
        $processIsolation = $processIsolation === 'none' ? false : $processIsolation;
        $parameters = null;

        if (!$configFile && empty($reports)) {
            $reports = array('simple_table');
        }

        Runner::validateProcessIsolation($processIsolation);

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

        $configuration = $this->getApplication()->getConfiguration();
        $enableGc = $input->getOption('gc-enable');
        $enableGc = $configuration->getGcEnabled() || $input->getOption('gc-enable');

        if ($configuration->getConfigPath()) {
            $consoleOutput->writeln(sprintf('Using configuration file: %s', $configuration->getConfigPath()));
        }

        $consoleOutput->writeln('');

        $progressLogger = $configuration->getProgress() ?: $input->getOption('progress');
        $progressLogger = $configuration->getProgressLogger($progressLogger);
        $progressLogger->setOutput($consoleOutput);

        $this->processReportConfigs($reports);
        $path = $input->getArgument('path') ?: $configuration->getPath();

        if (null === $path) {
            throw new \InvalidArgumentException(
                'You must either specify or configure a path where your benchmarks can be found.'
            );
        }

        $subjects = $input->getOption('subject');
        $groups = $input->getOption('group');
        $dumpfile = $input->getOption('dump-file');

        if (false === $enableGc) {
            gc_disable();
        }

        $startTime = microtime(true);
        $result = $this->executeBenchmarks($path, $subjects, $groups, $noSetup, $parameters, $iterations, $revs, $processIsolation, $configFile, $progressLogger);

        $consoleOutput->writeln('');
        if ($dumpfile) {
            $xml = $this->dumpResult($result);
            file_put_contents($dumpfile, $xml);
            $consoleOutput->writeln('<info>Dumped result to </info>' . $dumpfile);
        }
        $consoleOutput->writeln('');
        $consoleOutput->writeln(sprintf(
            '<greenbg>Done (%s subjects, %s iterations) in %ss</greenbg>',
            count($result->getSubjectResults()),
            count($result->getIterationResults()),
            number_format(microtime(true) - $startTime, 2)
        ));

        if ($dump) {
            $output->write($this->dumpResult($result));
        } elseif ($configuration->getReports()) {
            $this->generateReports($consoleOutput, $result);
        }
    }

    private function dumpResult(SuiteResult $result)
    {
        $dumper = new XmlDumper();

        return $dumper->dump($result);
    }

    private function executeBenchmarks($path, array $subjects, array $groups, $noSetup, $parameters, $iterations, $revs, $processIsolation, $configFile, ProgressLogger $progressLogger)
    {
        $finder = new Finder();

        if (!file_exists($path)) {
            throw new \InvalidArgumentException(sprintf(
                'File or directory "%s" does not exist',
                $path
            ));
        }

        if (is_dir($path)) {
            $finder->in($path);
            $finder->name('*Bench.php');
        } else {
            $finder->in(dirname($path));
            $finder->name(basename($path));
        }

        $benchFinder = new CollectionBuilder($finder);
        $subjectBuilder = new SubjectBuilder($subjects, $groups);

        $benchRunner = new Runner(
            $benchFinder,
            $subjectBuilder,
            $progressLogger,
            $processIsolation,
            !$noSetup,
            $parameters,
            $iterations,
            $revs,
            $configFile
        );

        return $benchRunner->runAll();
    }
}
