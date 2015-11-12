<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Report\Generator;

use PhpBench\Benchmark\SuiteDocument;
use PhpBench\Tabular\Definition\Loader;
use PhpBench\Tabular\Tabular;

/**
 * Simple report generator using preconfigured report definitions.
 */
class TabularGenerator extends AbstractTabularGenerator
{
    /**
     * @var Loader
     */
    private $definitionLoader;

    /**
     * @param Tabular $tabular
     * @param Loader $loader
     */
    public function __construct(Tabular $tabular, Loader $loader)
    {
        parent::__construct($tabular);
        $this->definitionLoader = $loader;
    }

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
                'pretty_params' => array(
                    'type' => 'boolean',
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
                'groups' => array(
                    'type' => 'array',
                ),
                'sort' => array(
                    'oneOf' => array(
                        array('type' => 'object'),
                        array('type' => 'array'),
                    ),
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
            $report = 'aggregate';
        } else {
            $report = 'iteration';
        }

        $reportFile = __DIR__ . '/tabular/' . $report . '.json';
        $definition = $this->definitionLoader->load($reportFile);

        if ($config['groups']) {
            $document = $this->filterGroups($document, $config['groups']);
        }

        if ($config['sort']) {
            $sort = array();
            // we need to prefix the group name
            foreach ($config['sort'] as $colSpec => $direction) {
                $sort['body#' . $colSpec] = $direction;
            }
            $definition['sort'] = $sort;
        }

        $parameters = array();
        if ($config['selector']) {
            $parameters['selector'] = $config['selector'];
        }

        return $this->doGenerate($definition, $document, $config, $parameters);
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
            'groups' => array(),
            'title' => null,
            'description' => null,
            'exclude' => array(),
            'aggregate' => false,
            'selector' => null,
            'sort' => array(),
        );
    }

    private function filterGroups(SuiteDocument $document, $groups)
    {
        $document = $document->duplicate();

        $exprs = array();
        foreach ($groups as $groupName) {
            $exprs[] = "not(group/@name='" . $groupName . "')";
        }
        $groupExpr = implode(' and ', $exprs);
        $expr = '//subject[' . $groupExpr . ']';
        $nodes = $document->xpath()->query($expr);
        foreach ($nodes as $node) {
            $node->parentNode->removeChild($node);
        }

        return $document;
    }
}
