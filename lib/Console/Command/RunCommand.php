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
        $this->addArgument('path', InputArgument::REQUIRED, 'Path to benchmark(s)');
        $this->addOption('report', array(), InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Report name or configuration in JSON format');
        $this->addOption('filter', null, InputOption::VALUE_REQUIRED, 'Filter subject(s) to run');
        $this->addOption('dumpfile', 'df', InputOption::VALUE_REQUIRED, 'Dump XML to named file');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Running benchmark suite</info>');
        $output->writeln('');
        $reportConfigs = $this->normalizeReportConfig($input->getOption('report'));

        $path = $input->getArgument('path');
        $filter = $input->getOption('filter');
        $dumpfile = $input->getOption('dumpfile');

        $startTime = microtime(true);
        $result = $this->executeBenchmarks($output, $path, $filter);

        $output->writeln('');

        if ($dumpfile) {
            $this->dumpResult($result, $dumpfile);
            $output->writeln('<info>Dumped result to </info>' . $dumpfile);
            $output->writeln('');
        }

        if ($reportConfigs) {
            $this->generateReports($output, $result, $reportConfigs);
        }

        $output->writeln(sprintf('<info>Done </info>(%s)', number_format(microtime(true) - $startTime, 6)));
        $output->writeln('');
    }

    private function dumpResult(SuiteResult $result, $dumpfile)
    {
        $dumper = new XmlDumper();
        $data = $dumper->dump($result);

        file_put_contents($dumpfile, $data);
    }

    private function executeBenchmarks(OutputInterface $output, $path, $filter)
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
        $subjectBuilder = new SubjectBuilder($filter);
        $progressLogger = new PhpUnitProgressLogger($output);

        $benchRunner = new Runner(
            $benchFinder,
            $subjectBuilder,
            $progressLogger
        );

        return $benchRunner->runAll();
    }
}
