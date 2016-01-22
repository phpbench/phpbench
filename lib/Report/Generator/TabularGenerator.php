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

use PhpBench\Dom\Document;
use PhpBench\Registry\Config;
use PhpBench\Tabular\Tabular;

/**
 * Simple report generator using preconfigured report definitions.
 */
class TabularGenerator extends AbstractTabularGenerator
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
                'pretty_params' => array(
                    'type' => 'boolean',
                ),
                'type' => array(
                    'type' => array(
                        'enum' => array(
                            'default',
                            'aggregate',
                            'compare',
                        ),
                    ),
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
                'formatting' => array(
                    'type' => 'boolean',
                ),
                'body_only' => array(
                    'type' => 'boolean',
                ),
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function generate(Document $document, Config $config)
    {
        $reportFile = __DIR__ . '/Tabular/' . $config['type'] . '.json';
        $definition = $this->definitionLoader->load($reportFile, $document);

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

        $result = $this->doGenerate($definition, $document, $config, $parameters);

        if (true === $config['body_only']) {
            foreach ($result->xpath()->query('//group[@name!="body"]') as $group) {
                $group->parentNode->removeChild($group);
            }
        }

        return $result;
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
            'type' => 'default',
            'selector' => null,
            'sort' => array(),
            'body_only' => false,
        );
    }

    private function filterGroups(Document $originalDocument, $groups)
    {
        // duplicate the document
        $document = new Document();
        $node = $document->importNode($originalDocument->firstChild, true);
        $document->appendChild($node);

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
