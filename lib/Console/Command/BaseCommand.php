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
use Symfony\Component\Console\Output\OutputInterface;
use PhpBench\Result\SuiteResult;
use PhpBench\ReportGenerator\ConsoleTableReportGenerator;
use PhpBench\ReportGenerator\XmlTableReportGenerator;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;

abstract class BaseCommand extends Command
{
    protected function generateReports(OutputInterface $output, SuiteResult $results, $reportConfigs)
    {
        $output->writeln('<info>Generating reports...</info>');
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

    protected function normalizeReportConfig($rawConfigs)
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
}
