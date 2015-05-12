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
use PhpBench\ReportGenerator\ConsoleTableReportGenerator;
use PhpBench\ReportGenerator\XmlTableReportGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use PhpBench\Benchmark\CollectionBuilder;
use PhpBench\Benchmark\SubjectBuilder;
use PhpBench\Benchmark\Runner;

class RunCommand extends Command
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
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Running benchmark suite</info>');
        $output->writeln('');
        $reportConfigs = $this->normalizeReportConfig($input->getOption('report'));

        $path = $input->getArgument('path');
        $filter = $input->getOption('filter');

        $startTime = microtime(true);
        $results = $this->executeBenchmarks($output, $path, $filter);

        $output->writeln('');

        $this->generateReports($output, $results, $reportConfigs);

        $output->writeln(sprintf('Done (%s)', number_format(microtime(true) - $startTime, 6)));
        $output->writeln('');
    }

    private function generateReports(OutputInterface $output, Benchmark\Collection $results, $reportConfigs)
    {
        $output->writeln('Generating reports...');
        $output->writeln('');

        $generators = array(
            'console_table' => new ConsoleTableReportGenerator($output),
            'xml_table' => new XmlTableReportGenerator($output),
        );

        foreach ($reportConfigs as $reportName => $reportConfig) {
            if (!isset($generators[$reportName])) {
                throw new \InvalidArgumentException(sprintf(
                    'Unknown report generator "%s", known generators: "%s"',
                    $reportName, implode('", "', array_keys($generators))
                ));
            }
        }

        foreach ($reportConfigs as $reportName => $reportConfig) {
            $options = new OptionsResolver();
            $report = $generators[$reportName];
            $report->configure($options);

            $output->writeln(sprintf('>> %s >>', $reportName));
            $output->writeln('');
            try {
                $reportConfig = $options->resolve($reportConfig);
            } catch (UndefinedOptionsException $e) {
                throw new \InvalidArgumentException(sprintf(
                    'Error generating report "%s"', $reportName
                ), null, $e);
            }

            $report->generate($results, $reportConfig);
        }
    }

    private function normalizeReportConfig($rawConfigs)
    {
        $configs = array();
        foreach ($rawConfigs as $rawConfig) {
            // If it doesn't look like a JSON string, assume it is the name of a report
            if (substr($rawConfig, 0, 1) !== '{') {
                $configs[$rawConfig] = array();
                continue;
            }

            $config = json_decode($rawConfig, true);

            if (null === $config) {
                throw new \InvalidArgumentException(sprintf(
                    'Could not decode JSON string: %s', $rawConfig
                ));
            }

            if (!isset($config['name'])) {
                throw new \InvalidArgumentException(sprintf(
                    'You must include the name of the report ("name") in the report configuration: %s',
                    $rawConfig
                ));
            }

            $name = $config['name'];
            unset($config['name']);

            $configs[$name] = $config;
        }

        return $configs;
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
