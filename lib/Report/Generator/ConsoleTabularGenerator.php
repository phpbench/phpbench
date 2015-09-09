<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Report\Generator;

use PhpBench\Benchmark\SuiteDocument;
use PhpBench\Tabular\Tabular;

/**
 * Simple report generator using preconfigured report definitions.
 */
class ConsoleTabularGenerator extends AbstractConsoleTabularGenerator
{
    /**
     * {@inheritdoc}
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
                    ),
                ),
                'description' => array(
                    'oneOf' => array(
                        array('type' => 'string'),
                        array('type' => 'null'),
                    ),
                ),
                'aggregate' => array(
                    'type' => 'boolean',
                ),
                'exclude' => array(
                    'type' => 'array',
                ),
                'debug' => array(
                    'type' => 'boolean',
                ),
                'selector' => array(
                    'oneOf' => array(
                        array('type' => 'string'),
                        array('type' => 'null'),
                    ),
                ),
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function generate(SuiteDocument $document, array $config)
    {
        if ($config['aggregate']) {
            $report = 'console_aggregate';
        } else {
            $report = 'console_iteration';
        }

        $reportFile = __DIR__ . '/tabular/' . $report . '.json';

        $parameters = array();
        if ($config['selector']) {
            $parameters['selector'] = $config['selector'];
        }

        $this->doGenerate($reportFile, $document, $config, $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultReports()
    {
        return array(
            'aggregate' => array(
                'aggregate' => true,
            ),
            'default' => array(
                'aggregate' => false,
            ),
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
            'exclude' => array(),
            'aggregate' => false,
            'selector' => null,
        );
    }
}
