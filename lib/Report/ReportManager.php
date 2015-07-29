<?php

namespace PhpBench\Report;

use PhpBench\ReportGenerator;
use Symfony\Component\Console\Output\OutputInterface;
use PhpBench\Result\SuiteResult;
use PhpBench\Console\OutputAware;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReportManager
{
    /**
     * @param array
     */
    private $reports = array();

    /**
     * Add a named report configuration.
     *
     * @param string $name
     * @param array $config
     */
    public function addReport($name, array $config)
    {
        if (isset($this->reports[$name])) {
            throw new \InvalidArgumentException(sprintf(
                'Report with name "%s" has already been registered',
                $name
            ));
        }

        $this->reports[$name] = $config;
    }

    /**
     * Add a report generator
     *
     * @param string $name
     * @param ReportGenerator $generator
     */
    public function addGenerator($name, ReportGenerator $generator)
    {
        if (isset($this->generators[$name])) {
            throw new \InvalidArgumentException(sprintf(
                'Report generator with name "%s" has already been registered',
                $name
            ));
        }

        $this->generators[$name] = $generator;

        $defaultReports = $generator->getDefaultReports();

        if (!is_array($defaultReports)) {
            throw new \RuntimeException(sprintf(
                'Method getDefaultReports on report generator "%s" must return an array, it is returning: "%s"',
                get_class($generator),
                is_object($defaultReports) ? get_class($defaultReports) : gettype($defaultReports)
            ));
        }

        foreach ($defaultReports as $reportName => $reportConfig) {
            $this->addReport($reportName, $reportConfig);
        }
    }

    /**
     * Process raw report configuration as recieved from the CLI, for example:
     * 
     * ````
     * {"generator": "console_table", "sort": ["time"]}
     * ````
     *
     * Or simply the name of a pre-configured report to use:
     *
     * ````
     * console_table
     * ````
     * Accepts an array of strings and returns the names of all reports that
     * have been identified / processed.
     *
     * Report configurations will be added to the report manager with a generated UUID.
     *
     * @param array $rawConfigs
     * @return array
     */
    public function processCliReports($rawConfigs)
    {
        $reportNames = array();
        foreach ($rawConfigs as $rawConfig) {
            // If it doesn't look like a JSON string, assume it is the name of a report
            if (substr($rawConfig, 0, 1) !== '{') {
                $reportNames[] = $rawConfig;
                continue;
            }

            $report = json_decode($rawConfig, true);

            if (null === $report) {
                throw new \InvalidArgumentException(sprintf(
                    'Could not decode JSON string: %s', $rawConfig
                ));
            }

            $reportName = uniqid();
            $reportNames[] = $reportName;

            $this->addReport($reportName, $report);
        }

        return $reportNames;
    }

    /**
     * Return the named report configuration
     *
     * @param string $name
     * @return array
     */
    public function getReport($name)
    {
        if (!isset($this->reports[$name])) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown report "%s", known reports: "%s"',
                $name, implode('", "', array_keys($this->reports))
            ));
        }

        return $this->reports[$name];
    }

    public function getGenerator($name)
    {
        if (!isset($this->generators[$name])) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown report generator "%s", known generator: "%s"',
                $name, implode('", "', array_keys($this->generators))
            ));
        }

        return $this->generators[$name];
    }

    /**
     * Generate the named reports.
     *
     * @param OutputInterface $output
     * @param SuiteResult $results
     * @param array $reportNames
     */
    public function generateReports(OutputInterface $output, SuiteResult $results, array $reportNames)
    {
        $reportConfigs = array();
        foreach ($reportNames as $reportName) {
            $reportConfigs[$reportName] = $this->getReport($reportName);
        }

        foreach ($reportConfigs as $reportName => $reportConfig) {
            $reportConfig = $this->resolveReportConfig($reportConfig);

            if (!isset($reportConfig['generator'])) {
                throw new \InvalidArgumentException(
                    'Each report config must specify a generator (e.g. console_table)'
                );
            }

            $options = new OptionsResolver();
            $generator = $this->getGenerator($reportConfig['generator']);
            $generator->configure($options);
            unset($reportConfig['generator']);

            try {
                $reportConfig = $options->resolve($reportConfig);
            } catch (UndefinedOptionsException $e) {
                throw new \InvalidArgumentException(sprintf(
                    'Error generating report "%s"', $reportName
                ), null, $e);
            }

            if ($generator instanceof OutputAware) {
                $generator->setOutput($output);
            }

            $generator->generate($results, $reportConfig);
        }
    }

    private function resolveReportConfig(array $reportConfig)
    {
        if (isset($reportConfig['extends'])) {
            $extended = $this->getReport($reportConfig['extends']);
            unset($reportConfig['extends']);
            $reportConfig = array_merge(
                $this->resolveReportConfig($extended),
                $reportConfig
            );
        }

        return $reportConfig;
    }
}
