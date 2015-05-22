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

use PhpBench\ProgressLogger\PhpUnitProgressLogger;
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
        $this->addOption('subject', null, InputOption::VALUE_REQUIRED, 'Subject to run');
        $this->addOption('dumpfile', 'df', InputOption::VALUE_OPTIONAL, 'Dump XML result to named file');
        $this->addOption('dump', null, InputOption::VALUE_NONE, 'Dump XML result to stdout');
        $this->addOption('parameters', null, InputOption::VALUE_REQUIRED, 'Explicit parameters to use in benchmark');
        $this->addOption('nosetup', null, InputOption::VALUE_REQUIRED, 'Do not execute setUp or tearDown methods');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $reports = $input->getOption('report');
        $consoleOutput = $output;
        $dump = $input->getOption('dump');
        $parametersJson = $input->getOption('parameters');
        $noSetup = $input->getOption('nosetup');
        $parameters = null;

        if ($parametersJson) {
            $parameters = json_decode($parametersJson, true);
            if (!$parameters) {
                throw new \InvalidArgumentException(sprintf(
                    'Could not decode parameters JSON string: "%s"', $parametersJson
                ));
            }
        }

        if ($dump) {
            $consoleOutput = new NullOutput();
        }

        $consoleOutput->writeln('<info>Running benchmark suite</info>');
        $consoleOutput->writeln('');

        $configuration = $this->getApplication()->getConfiguration();

        $this->processReportConfigs($reports);
        $path = $input->getArgument('path') ?: $configuration->getPath();

        if (null === $path) {
            throw new \InvalidArgumentException(
                'You must either specify or configure a path where your benchmarks can be found.'
            );
        }

        $subject = $input->getOption('subject');
        $dumpfile = $input->getOption('dumpfile');

        $startTime = microtime(true);
        $result = $this->executeBenchmarks($consoleOutput, $path, $subject, $noSetup, $parameters);

        $consoleOutput->writeln('');

        if ($dumpfile) {
            $xml = $this->dumpResult($result);
            file_put_contents($dumpfile, $xml);
            $consoleOutput->writeln('<info>Dumped result to </info>' . $dumpfile);
            $consoleOutput->writeln('');
        }

        if ($dump) {
            $output->write($this->dumpResult($result));
        }

        if ($configuration->getReports()) {
            $this->generateReports($consoleOutput, $result);
        }

        $consoleOutput->writeln(sprintf('<info>Done </info>(%s)', number_format(microtime(true) - $startTime, 6)));
        $consoleOutput->writeln('');
    }

    private function dumpResult(SuiteResult $result)
    {
        $dumper = new XmlDumper();
        return $dumper->dump($result);
    }

    private function executeBenchmarks(OutputInterface $output, $path, $subject, $noSetup, $parameters)
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
        } else {
            $finder->in(dirname($path));
            $finder->name(basename($path));
        }

        $benchFinder = new CollectionBuilder($finder);
        $subjectBuilder = new SubjectBuilder($subject);
        $progressLogger = new PhpUnitProgressLogger($output);

        $benchRunner = new Runner(
            $benchFinder,
            $subjectBuilder,
            $progressLogger
        );

        return $benchRunner->runAll($noSetup, $parameters);
    }
}
