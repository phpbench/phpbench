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

use Functional as F;
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
class TableGenerator implements GeneratorInterface, OutputAwareInterface
{
    private $output;
    private $statKeys;
    private $classMap = [
        'best' => ['timeunit'],
        'worst' => ['timeunit'],
        'mean' => ['timeunit'],
        'mode' => ['timeunit'],
        'stdev' => ['timeunit'],
        'time' => ['timeunit'],
        'rstdev' => ['percentage'],
        'mem' => ['mem'],
        'diff' => ['diff'],
        'z-value' => ['z-value'],
    ];

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
        return [
            'title' => null,
            'description' => null,
            'cols' => ['benchmark', 'subject', 'groups', 'params', 'revs', 'its', 'mem', 'best', 'mean', 'mode', 'worst', 'stdev', 'rstdev', 'diff'],
            'break' => ['suite'],
            'compare' => null,
            'compare_fields' => ['mean'],
            'diff_col' => 'mean',
            'sort' => [],
            'pretty_params' => false,
            'iterations' => false,
        ];
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
     * Calculate the ``diff`` column if it is displayed.
     *
     * @param array $tables
     * @param Config $config
     *
     * @return array
     */
    private function processDiffs(array $tables, Config $config)
    {
        $stat = $config['diff_col'];

        if ($config['compare']) {
            return $tables;
        }

        if (!in_array('diff', $config['cols'])) {
            return $tables;
        }

        if (!in_array($stat, $config['cols'])) {
            throw new \InvalidArgumentException(sprintf(
                'The "%s" column must be visible when using the diff column',
                $stat
            ));
        }

        return F\map($tables, function ($table) use ($stat) {
            $means = F\map($table, function ($row) use ($stat) {
                return $row[$stat];
            });
            $min = min($means);

            return F\map($table, function ($row) use ($min, $stat) {
                $row['diff'] = (100 / $row[$stat]) * ($row[$stat] - $min);

                return $row;
            });
        });
    }

    /**
     * Process the sorting, also break sorting.
     *
     * @param array $table
     * @param Config $config
     *
     * @return array
     */
    private function processSort(array $table, Config $config)
    {
        if ($config['sort']) {
            $cols = array_reverse($config['sort']);
            foreach ($cols as $colName => $direction) {
                Sort::mergeSort($table, function ($elementA, $elementB) use ($colName, $direction) {
                    if ($elementA[$colName] == $elementB[$colName]) {
                        return 0;
                    }

                    if ($direction === 'asc') {
                        return $elementA[$colName] < $elementB[$colName] ? -1 : 1;
                    }

                    return $elementA[$colName] > $elementB[$colName] ? -1 : 1;
                });
            }
        }

        if ($config['break']) {
            foreach ($config['break'] as $colName) {
                Sort::mergeSort($table, function ($elementA, $elementB) use ($colName) {
                    if ($elementA[$colName] == $elementB[$colName]) {
                        return 0;
                    }

                    return $elementA[$colName] < $elementB[$colName] ? -1 : 1;
                });
            }
        }

        return $table;
    }

    /**
     * Process breaks (split large table into smaller tables).
     *
     * @param array $table
     * @param Config $config
     *
     * @return array
     */
    private function processBreak(array $table, Config $config)
    {
        if (!$config['break']) {
            return [$table];
        }

        $break = $config['break'];

        foreach ($break as $breakKey) {
            // remove the break col from the visible cols.
            if (false !== $index = array_search($breakKey, $config['cols'])) {
                $cols = $config['cols'];
                unset($cols[$index]);
                $config['cols'] = $cols;
            }
        }

        return F\group($table, function ($row) use ($break) {
            $breakHash = [];
            foreach ($break as $breakKey) {
                $breakHash[] = $breakKey. ': ' .$row[$breakKey];
                unset($row[$breakKey]);
            }

            return implode(', ', $breakHash);
        });
    }

    /**
     * Remove unwanted columns from the tables.
     *
     * @param array $tables
     * @param array $config
     *
     * @return array
     */
    private function processCols(array $tables, Config $config)
    {
        if ($config['cols']) {
            $cols = $config['cols'];
            if ($config['compare']) {
                $cols[] = $config['compare'];
                $cols = array_merge($cols, $config['compare_fields']);
            }
            $tables = F\map($tables, function ($table) use ($cols) {
                return F\map($table, function ($row) use ($cols) {
                    $newRow = $row->newInstance([]);
                    foreach ($cols as $col) {
                        if ($col === 'diff') {
                            continue;
                        }
                        $newRow[$col] = $row[$col];
                    }

                    return $newRow;
                });
            });
        }

        return $tables;
    }

    /**
     * Process the compare feature.
     *
     * @param array $tables
     * @param Config $config
     *
     * @return array
     */
    private function processCompare(array $tables, Config $config)
    {
        if (!isset($config['compare'])) {
            return $tables;
        }

        $conditions = array_diff($config['cols'], $this->statKeys, [$config['compare']]);
        $compare = $config['compare'];
        $compareFields = $config['compare_fields'];

        return F\map($tables, function ($table) use ($conditions, $compare, $compareFields) {
            $groups = F\group($table, function ($row) use ($conditions, $compare, $compareFields) {
                $values = array_intersect_key($row->getArrayCopy(), array_flip($conditions));

                return F\reduce_left($values, function ($value, $i, $c, $reduction) {
                    return $reduction . $value;
                });
            });

            $table = [];
            $colNames = null;
            foreach ($groups as $group) {
                $firstRow = null;
                foreach ($group as $row) {
                    if (null === $firstRow) {
                        $firstRow = $row->newInstance(array_diff_key($row->getArrayCopy(), array_flip($this->statKeys)));
                        unset($firstRow[$compare]);
                    }

                    if (null === $colNames) {
                        $colNames = array_combine($firstRow->getNames(), $firstRow->getNames());
                    }

                    $compared = $row[$compare];

                    foreach ($compareFields as $compareField) {
                        $name = $compare . ':' . $compared . ':' . $compareField;

                        $name = $this->resolveCompareColumnName($firstRow, $name);

                        $firstRow[$name] = $row[$compareField];
                        $colNames[$name] = $name;

                        // we invent a new col name here, use the compare field's class.
                        $this->classMap[$name] = $this->classMap[$compareField];
                    }
                }

                $table[] = $firstRow;
            }

            $table = F\map($table, function ($row) use ($colNames) {
                $newRow = $row->newInstance([]);
                foreach ($colNames as $colName) {
                    $newRow[$colName] = isset($row[$colName]) ? $row[$colName] : null;
                }

                return $newRow;
            });

            return $table;
        });

        return $tables;
    }

    /**
     * Construct the initial table from the SuiteCollection.
     *
     * @param SuiteCollection $suiteCollection
     * @param Config $config
     *
     * @return array
     */
    private function buildTable(SuiteCollection $suiteCollection, Config $config)
    {
        $paramJsonFlags = null;
        if (true === $config['pretty_params']) {
            $paramJsonFlags = JSON_PRETTY_PRINT;
        }

        $table = [];
        foreach ($suiteCollection->getSuites() as $suiteIndex => $suite) {
            $env = $suite->getEnvInformations();

            foreach ($suite->getBenchmarks() as $benchmark) {
                foreach ($benchmark->getSubjects() as $subject) {
                    foreach ($subject->getVariants() as $variant) {
                        $row = new Row([
                            'suite' => $suite->getIdentifier(),
                            'date' => $suite->getDate()->format('Y-m-d'),
                            'stime' => $suite->getDate()->format('H:i:s'),
                            'benchmark' => $this->getClassShortName($benchmark->getClass()),
                            'benchmark_full' => $benchmark->getClass(),
                            'subject' => $subject->getName(),
                            'groups' => implode(',', $subject->getGroups()),
                            'params' => json_encode($variant->getParameterSet()->getArrayCopy(), $paramJsonFlags),
                            'revs' => $variant->getRevolutions(),
                            'its' => count($variant->getIterations()),
                            'mem' => Statistics::mean($variant->getMemories()),
                        ]);

                        // the formatter params are passed to the Formatter and
                        // allow the formatter configurations to use tokens --
                        // in other words we can override formatting on a
                        // per-row basis.
                        $formatParams = [];
                        if ($timeUnit = $subject->getOutputTimeUnit()) {
                            $formatParams['output_time_unit'] = $timeUnit;
                        }

                        if ($mode = $subject->getOutputMode()) {
                            $formatParams['output_mode'] = $mode;
                        };

                        if ($precision = $subject->getOutputTimePrecision()) {
                            $formatParams['output_time_precision'] = $precision;
                        };
                        $row->setFormatParams($formatParams);

                        $stats = $variant->getStats()->getStats();
                        $stats['best'] = $stats['min'];
                        $stats['worst'] = $stats['max'];

                        // save on duplication and lazily evaluate the
                        // available statistics.
                        if (null === $this->statKeys) {
                            $this->statKeys = array_keys($stats);
                        }

                        $row = $row->merge($stats);

                        // generate the environment parameters.
                        // TODO: should we crash here if an attempt is made to
                        //       override a row?  it could happen.
                        foreach ($env as $providerName => $information) {
                            foreach ($information as $key => $value) {
                                $row[$providerName . '_' . $key] = $value;
                            }
                        }

                        // if the iterations option is specified then we add a row for each iteration, otherwise
                        // we continue.
                        if (false === $config['iterations']) {
                            $table[] = $row;
                            continue;
                        }

                        foreach ($variant->getIterations() as $index => $iteration) {
                            $row = clone $row;
                            $row['iter'] = $index;
                            $row['rej'] = $iteration->getRejectionCount();
                            $row['time'] = $iteration->getTime();
                            $row['z-value'] = $iteration->getZValue();
                            $table[] = $row;
                        }
                    }
                }
            }
        }

        return $table;
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

    /**
     * Return the short name of a fully qualified class name.
     *
     * @param string $fullName
     */
    private function getClassShortName($fullName)
    {
        $parts = explode('\\', $fullName);
        end($parts);

        return current($parts);
    }

    /**
     * Recursively resolve a comparison column - find a column name that
     * doesn't already exist by adding and incrementing an index.
     *
     * @param Row $row
     * @param int $index
     *
     * @return string
     */
    private function resolveCompareColumnName(Row $row, $name, $index = 1)
    {
        if (!isset($row[$name])) {
            return $name;
        }

        $newName = $name . '#' . (string) $index++;

        if (!isset($row[$newName])) {
            return $newName;
        }

        return $this->resolveCompareColumnName($row, $name, $index);
    }
}
