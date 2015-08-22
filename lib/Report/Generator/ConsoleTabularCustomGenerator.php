<?php

namespace PhpBench\Report\Generator;

use PhpBench\Tabular\Tabular;
use PhpBench\ReportGeneratorInterface;
use PhpBench\Benchmark\SuiteDocument;
use Symfony\Component\Console\Helper\Table;
use PhpBench\Console\OutputAwareInterface;

class ConsoleTabularCustomGenerator extends AbstractConsoleTabularGenerator
{
    private $configPath;

    public function __construct(Tabular $tabular, $configPath)
    {
        $this->tabular = $tabular;
        $this->configPath = $configPath;
    }

    /**
     * {@inheritDoc}
     */
    public function getSchema()
    {
        return array(
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => array(
                'title' => array(
                    'oneOf' => array(
                        array('type' => 'string'),
                        array('type' => 'null'),
                    )
                ),
                'description' => array(
                    'oneOf' => array(
                        array('type' => 'string'),
                        array('type' => 'null'),
                    )
                ),
                'file' => array(
                    'oneOf' => array(
                        array('type' => 'string'),
                        array('type' => 'null'),
                    )
                ),
                'params' => array(
                    'oneOf' => array(
                        array('type' => 'object'),
                        array('type' => 'array'),
                    )
                ),
                'debug' => array(
                    'type' => 'boolean',
                ),
            ),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function generate(SuiteDocument $document, array $config)
    {
        if (!$config['file']) {
            throw new \InvalidArgumentException(
                'You must provide the path to a Tabular JSON report definition with the "file" option.'
            );
        }

        $reportFile = $config['file'];

        // if not an absolute path, make it relative to the config file
        if (substr($reportFile, 0, 1) !== '/') {
            $reportFile = dirname($this->configPath) . '/' . $reportFile;
        }

        $parameters = $config['params'];
        $this->doGenerate($reportFile, $document, $config, $parameters);
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultReports()
    {
        return array(
        );
    }

    /***
     * {@inheritDoc}
     */
    public function getDefaultConfig()
    {
        return array(
            'debug' => false,
            'title' => null,
            'description' => null,
            'file' => null,
            'params' => array(),
        );
    }
}
