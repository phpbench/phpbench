<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Report;

use PhpBench\ReportGeneratorInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PhpBench\Benchmark\SuiteDocument;
use PhpBench\Console\OutputAwareInterface;
use JsonSchema\Validator;

/**
 * Manage report configuration and generation.
 */
class ReportManager
{
    /**
     * @var array
     */
    private $reports = array();

    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var ReportGeneratorInterface[]
     */
    private $generators;

    public function __construct(
        Validator $validator = null
    ) {
        $this->validator = $validator ?: new Validator();
    }

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
     * Add a report generator.
     *
     * @param string $name
     * @param ReportGenerator $generator
     */
    public function addGenerator($name, ReportGeneratorInterface $generator)
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
            $reportConfig['generator'] = $name;
            $this->addReport($reportName, $reportConfig);
        }
    }

    /**
     * Process raw report configuration as recieved from the CLI, for example:.
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
     * have been identified / processed.n
     *
     * Report configurations will be added to the report manager with a generated UUID.
     *
     * @param array $rawConfigs
     *
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
     * Return the named report configuration.
     *
     * @param string $name
     *
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

    /**
     * Return the named generator.
     *
     * @return ReportGenerator
     */
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
     * @param SuiteDocument $suiteDocument
     * @param array $reportNames
     */
    public function generateReports(OutputInterface $output, SuiteDocument $suiteDocument, array $reportNames)
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

            $generator = $this->getGenerator($reportConfig['generator']);
            $reportConfig = array_replace_recursive($generator->getDefaultConfig(), $reportConfig);

            // not sure if there is a better way to convert the schema array to objects
            // as expected by the validator.
            $validationConfig = json_decode(json_encode($reportConfig));
            $schema = json_decode(json_encode($generator->getSchema()));
            $this->validator->check($validationConfig, $schema);

            if (!$this->validator->isValid()) {
                $errorString = array();
                foreach ($this->validator->getErrors() as $error) {
                    $errorString[] = sprintf('[%s] %s', $error['property'], $error['message']);
                }
                throw new \InvalidArgumentException(sprintf(
                    'Invalid JSON when processing report "%s": %s%s',
                    $reportName, PHP_EOL . PHP_EOL . PHP_EOL, implode(PHP_EOL, $errorString)
                ));
            }

            unset($reportConfig['generator']);

            if ($generator instanceof OutputAwareInterface) {
                $generator->setOutput($output);
            }

            $generator->generate($suiteDocument, $reportConfig);
        }
    }

    private function resolveReportConfig(array $reportConfig)
    {
        if (isset($reportConfig['extends'])) {
            $extended = $this->getReport($reportConfig['extends']);
            unset($reportConfig['extends']);
            $reportConfig = array_replace_recursive(
                $this->resolveReportConfig($extended),
                $reportConfig
            );
        }

        return $reportConfig;
    }
}
