<?php

namespace PhpBench\Report;

use PhpBench\ReportGenerator;
use Symfony\Component\Console\Output\OutputInterface;
use PhpBench\Result\SuiteResult;
use PhpBench\OptionsResolver\OptionsResolver;
use PhpBench\Console\OutputAware;

class ReportManager
{
    /**
     * @param array
     */
    private $reports = array();

    public function __construct()
    {
        $this->registerDefaultReports();
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
        $this->reports[$name] = array('generator' => 'console_table');
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

        foreach ($reportConfigs as $reportConfig) {
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

    private function registerDefaultReports()
    {
        $this->addReport(
            'console_aggregate',
            array(
                'extends' => 'console_table',
                'selector' => '//iterations',
                'headers' => array('Description', 'Sum Revs.', 'Nb. Iters.', 'Av. Time', 'Av. RPS', 'Stability', 'Deviation'),
                'cells' => array(
                    'description' => 'string(../@description)',
                    'revs' => 'number(sum(.//@revs))',
                    'iters' => 'number(count({selector}))',
                    'time' => 'number(php:bench(\'avg\', .//iteration/@time))',
                    'rps' => '(1000000 div number(php:bench(\'avg\', .//iteration/@time)) * number(php:bench(\'avg\', (.//iteration/@revs))))',
                    'stability' => '100 - php:bench(\'deviation\', number(php:bench(\'min\', ./iteration/@time)), number(php:bench(\'avg\', ./iteration/@time)))',
                    'deviation' => 'number(php:bench(\'deviation\', number(php:bench(\'min\', //cell[@name="time"])), number(./cell[@name="time"])))'
                ),
                'post-process' => array(
                    'deviation',
                ),
                'format' => array(
                    'revs' => '!number',
                    'rps' => array('!number', '%s<comment>rps</comment>'),
                    'time' => array('!number', '%s<comment>μs</comment>'),
                    'stability' => array('%.2f', '%s<comment>%%</comment>'),
                    'deviation' => array('%.2f', '!balance', '%s<comment>%%</comment>'),
                ),
                'sort' => array('time' => 'asc'),
            )
        );

        $this->addReport(
            'console_simple',
            array(
                'extends' => 'console_table',
                'headers' => array('Description', 'Sum Revs.', 'Nb. Iters.', 'Av. Time', 'Av. RPS', 'Deviation'),
                'exclude' => ["subject", "group"],
            )
        );
    }
}
