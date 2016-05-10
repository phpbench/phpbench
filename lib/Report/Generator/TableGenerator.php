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

use PhpBench\Console\OutputAwareInterface;
use PhpBench\Dom\Document;
use PhpBench\Math\Statistics;
use PhpBench\Model\SuiteCollection;
use PhpBench\Registry\Config;
use PhpBench\Report\Generator\Table\Row;
use PhpBench\Report\Generator\Table\Sort;
use PhpBench\Report\GeneratorInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The table generator generates reports about benchmarking results.
 *
 * NOTE: This class could be improved, and perhaps even generalized.
 */
class TableGenerator extends AbstractTableGenerator implements OutputAwareInterface
{
    private $output;

    /**
     * {@inheritdoc}
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultConfig()
    {
        return array_merge(
            parent::getDefaultConfig(),
            [
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getSchema()
    {
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => [
                'title' => [
                    'type' => ['string', 'null'],
                ],
                'description' => [
                    'type' => ['string', 'null'],
                ],
                'cols' => [
                    'type' => 'array',
                ],
                'col_labels' => [
                    'type' => ['object', 'array'],
                ],
                'break' => [
                    'type' => 'array',
                ],
                'compare' => [
                    'type' => ['string', 'null'],
                ],
                'compare_fields' => [
                    'type' => 'array',
                ],
                'diff_col' => [
                    'type' => ['string', 'null'],
                ],
                'sort' => [
                    'type' => ['array', 'object'],
                ],
                'pretty_params' => [
                    'type' => 'boolean',
                ],
                'iterations' => [
                    'type' => 'boolean',
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function generate(SuiteCollection $suiteCollection, Config $config)
    {
        $table = $this->buildTable($suiteCollection, $config);

        $table = $this->processSort($table, $config);
        $tables = $this->processBreak($table, $config);
        $tables = $this->processCols($tables, $config);
        $tables = $this->processCompare($tables, $config);
        $tables = $this->processDiffs($tables, $config);

        return $this->generateDocument($tables, $config);
    }

    /**
     * Generate the report DOM document to pass to the report renderer.
     *
     * @param array $tables
     * @param Config $config
     *
     * @return Document
     */
    private function generateDocument(array $tables, Config $config)
    {
        $document = new Document();
        $reportsEl = $document->createRoot('reports');
        $reportsEl->setAttribute('name', 'table');
        $reportEl = $reportsEl->appendElement('report');

        if (isset($config['title'])) {
            $reportEl->setAttribute('title', $config['title']);
        }

        if (isset($config['description'])) {
            $reportEl->appendElement('description', $config['description']);
        }

        foreach ($tables as $breakHash => $table) {
            $tableEl = $reportEl->appendElement('table');

            // Build the col(umn) definitions.
            foreach ($table as $row) {
                $colsEl = $tableEl->appendElement('cols');
                foreach ($row->getNames() as $cellIndex => $colName) {
                    $colEl = $colsEl->appendElement('col');
                    $colEl->setAttribute('name', $colName);

                    // column labels are the column names by default.
                    // the user may override by column name or column index.
                    $colLabel = $colName;
                    if (isset($config['col_labels'][$colName])) {
                        $colLabel = $config['col_labels'][$colName];
                    } elseif (isset($config['col_labels'][$cellIndex])) {
                        $colLabel = $config['col_labels'][$cellIndex];
                    }

                    $colEl->setAttribute('label', $colLabel);
                }
                break;
            }

            if ($breakHash) {
                $tableEl->setAttribute('title', $breakHash);
            }

            $groupEl = $tableEl->appendElement('group');
            $groupEl->setAttribute('name', 'body');

            foreach ($table as $row) {
                $rowEl = $groupEl->appendElement('row');

                // apply formatter options
                foreach ($row->getFormatParams() as $paramName => $paramValue) {
                    $paramEl = $rowEl->appendElement('formatter-param', $paramValue);
                    $paramEl->setAttribute('name', $paramName);
                }

                foreach ($row as $key => $value) {
                    $cellEl = $rowEl->appendElement('cell', $value);
                    $cellEl->setAttribute('name', $key);

                    if (isset($this->classMap[$key])) {
                        $cellEl->setAttribute('class', implode(' ', $this->classMap[$key]));
                    }
                }
            }
        }

        return $document;
    }
}
