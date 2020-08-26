<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace PhpBench\Report\Generator;

use function Functional\group;
use function Functional\map;
use function Functional\reduce_left;
use PhpBench\Dom\Document;
use PhpBench\Math\Statistics;
use PhpBench\Model\Result\MemoryResult;
use PhpBench\Model\Result\TimeResult;
use PhpBench\Model\SuiteCollection;
use PhpBench\Model\Variant;
use PhpBench\Registry\Config;
use PhpBench\Report\Generator\Table\Cell;
use PhpBench\Report\Generator\Table\Row;
use PhpBench\Report\Generator\Table\SecondaryValue;
use PhpBench\Report\Generator\Table\Sort;
use PhpBench\Report\Generator\Table\ValueRole;
use PhpBench\Report\GeneratorInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The table generator generates reports about benchmarking results.
 *
 * NOTE: This class could be improved, and perhaps even generalized.
 */
class TableGenerator implements GeneratorInterface
{
    /**
     * @var array<int,string|int>
     */
    private $statKeys;

    /**
     * @var array<string,array<string>>
     */
    private $classMap = [
        'best' => ['timeunit'],
        'worst' => ['timeunit'],
        'mean' => ['timeunit'],
        'mode' => ['timeunit'],
        'stdev' => ['timeunit'],
        'rstdev' => ['percentage'],
        'baseline_best' => ['timeunit'],
        'baseline_worst' => ['timeunit'],
        'baseline_mean' => ['timeunit'],
        'baseline_mode' => ['timeunit'],
        'baseline_stdev' => ['timeunit'],
        'baseline_rstdev' => ['percentage'],
        'time_rev' => ['timeunit'],
        'time_net' => ['timeunit'],
        'mem_peak' => ['mem'],
        'mem_real' => ['mem'],
        'mem_final' => ['mem'],
        'diff' => ['diff'],
        'comp_deviation' => ['deviation'],
        'comp_z_value' => ['z-value'],
    ];

    /**
     * {@inheritdoc}
     */
    public function configure(OptionsResolver $options): void
    {
        $options->setDefaults([
            'title' => null,
            'description' => null,
            'cols' => ['benchmark', 'subject', 'tag', 'groups', 'params', 'revs', 'its', 'mem_peak', 'best', 'mean', 'mode', 'worst', 'stdev', 'rstdev', 'diff'],
            'baseline_cols' => ['mean', 'mode', 'rstdev', 'mem_peak'],
            'break' => ['tag', 'suite', 'date', 'stime'],
            'compare' => null,
            'compare_fields' => ['mean'],
            'diff_col' => 'mean',
            'sort' => [],
            'pretty_params' => false,
            'iterations' => false,
            'labels' => [],
            'class_map' => [],
        ]);

        $options->setAllowedTypes('title', ['null', 'string']);
        $options->setAllowedTypes('description', ['null', 'string']);
        $options->setAllowedTypes('cols', 'array');
        $options->setAllowedTypes('labels', 'array');
        $options->setAllowedTypes('break', 'array');
        $options->setAllowedTypes('compare', ['null', 'string']);
        $options->setAllowedTypes('compare_fields', 'array');
        $options->setAllowedTypes('diff_col', 'string');
        $options->setAllowedTypes('sort', 'array');
        $options->setAllowedTypes('pretty_params', 'bool');
        $options->setAllowedTypes('iterations', 'bool');
        $options->setAllowedTypes('class_map', 'array');
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
     * @param array<array<Row>> $tables
     *
     * @return array<array<Row>>
     */
    private function processDiffs(array $tables, Config $config): array
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

        return map($tables, function ($table) use ($stat) {
            $means = map($table, function (Row $row) use ($stat) {
                return $row->getValue($stat);
            });
            $min = min($means);

            return map($table, function (Row $row) use ($min, $stat) {
                if ($min === 0 || $min === 0.0) {
                    $row->setValue('diff', 0);

                    return $row;
                }

                $row->setValue('diff', $row->getValue($stat) / $min);

                return $row;
            });
        });
    }

    /**
     * Process the sorting, also break sorting.
     *
     * @param array<Row> $table
     * @param Config $config
     *
     * @return array<Row>
     */
    private function processSort(array $table, Config $config)
    {
        if ($config['sort']) {
            $cols = array_reverse($config['sort']);

            foreach ($cols as $colName => $direction) {
                Sort::mergeSort($table, function (Row $elementA, Row $elementB) use ($colName, $direction) {
                    if ($elementA->getValue($colName) == $elementB->getValue($colName)) {
                        return 0;
                    }

                    if ($direction === 'asc') {
                        return $elementA->getValue($colName) < $elementB->getValue($colName) ? -1 : 1;
                    }

                    return $elementA->getValue($colName) > $elementB->getValue($colName) ? -1 : 1;
                });
            }
        }

        if ($config['break']) {
            foreach ($config['break'] as $colName) {
                Sort::mergeSort($table, function (Row $elementA, Row $elementB) use ($colName) {
                    if ($elementA->getValue($colName) == $elementB->getValue($colName)) {
                        return 0;
                    }

                    return $elementA->getValue($colName) < $elementB->getValue($colName) ? -1 : 1;
                });
            }
        }

        return $table;
    }

    /**
     * Process breaks (split large table into smaller tables).
     *
     * @param array<Row> $table
     * @param Config $config
     *
     * @return array<array<Row>>
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

        return group($table, function (Row $row) use ($break) {
            $breakHash = [];

            foreach ($break as $breakKey) {
                $value = $row->getValue($breakKey);

                if (null !== $value) {
                    $breakHash[] = $breakKey. ': ' .$value;
                }
                $row->removeColumn($breakKey);
            }

            return implode(', ', $breakHash);
        });
    }

    /**
     * Remove unwanted columns from the tables.
     *
     * @param array<array<Row>> $tables
     * @param Config $config
     *
     * @return array<array<Row>>
     */
    private function processCols(array $tables, Config $config)
    {
        if ($config['cols']) {
            $cols = $config['cols'];

            if ($config['compare']) {
                $cols[] = $config['compare'];
                $cols = array_merge($cols, $config['compare_fields']);
            }

            $tables = map($tables, function ($table) use ($cols) {
                return map($table, function (Row $row) use ($cols) {
                    $newRow = $row->newInstance([]);

                    foreach ($cols as $col) {
                        if ($col === 'diff') {
                            continue;
                        }
                        $newRow->addCell($col, $row->getCell($col));
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
     * @param array<array<Row>> $tables
     * @param Config $config
     *
     * @return array<array<Row>>
     */
    private function processCompare(array $tables, Config $config)
    {
        if (!isset($config['compare'])) {
            return $tables;
        }

        $conditions = array_diff($config['cols'], $this->statKeys, [$config['compare']]);
        $compare = $config['compare'];
        $compareFields = $config['compare_fields'];

        return map($tables, function ($table) use ($conditions, $compare, $compareFields) {
            $groups = group($table, function (Row $row) use ($conditions) {
                $values = array_intersect_key($row->toArray(), array_flip($conditions));

                return reduce_left($values, function ($value, $i, $c, $reduction) {
                    return $reduction . $value;
                });
            });

            $table = [];
            $colNames = null;

            foreach ($groups as $group) {
                $firstRow = null;

                foreach ($group as $row) {
                    assert($row instanceof Row);

                    if (null === $firstRow) {
                        $firstRow = $row->newInstance(array_diff_key($row->toArray(), array_flip($this->statKeys)));

                        if ($firstRow->hasColumn($compare)) {
                            $firstRow->removeColumn($compare);
                        }

                        foreach ($compareFields as $compareField) {
                            if ($firstRow->hasColumn($compareField)) {
                                $firstRow->removeColumn($compareField);
                            }
                        }
                    }

                    if (null === $colNames) {
                        $colNames = (array)array_combine($firstRow->getNames(), $firstRow->getNames());
                    }

                    $compared = $row->getValue($compare);

                    foreach ($compareFields as $compareField) {
                        $name = $compare . ':' . $compared . ':' . $compareField;

                        $name = $this->resolveCompareColumnName($firstRow, $name);

                        $firstRow->setValue($name, $row->getValue($compareField));
                        $colNames[$name] = $name;

                        // TODO: This probably means the field is non-comparable, could handle this earlier..
                        if (isset($this->classMap[$compareField])) {
                            // we invent a new col name here, use the compare field's class.
                            $this->classMap[$name] = $this->classMap[$compareField];
                        }
                    }
                }

                $table[] = $firstRow;
            }

            $table = map($table, function (Row $row) use ($colNames) {
                $newRow = $row->newInstance([]);

                foreach ($colNames as $colName) {
                    $newRow->setValue($colName, $row->hasColumn($colName) ? $row->getValue($colName) : null);
                }

                return $newRow;
            });

            return $table;
        });
    }

    /**
     * Construct the initial table from the SuiteCollection.
     *
     * @param SuiteCollection $suiteCollection
     * @param Config $config
     *
     * @return array<Row>
     */
    private function buildTable(SuiteCollection $suiteCollection, Config $config)
    {
        $paramJsonFlags = null;

        if (true === $config['pretty_params']) {
            $paramJsonFlags = JSON_PRETTY_PRINT;
        }

        $table = [];
        $columnNames = [];

        foreach ($suiteCollection->getSuites() as $suite) {
            foreach ($suite->getBenchmarks() as $benchmark) {
                foreach ($benchmark->getSubjects() as $subject) {
                    foreach ($subject->getVariants() as $variant) {
                        $row = $this->buildRow($variant, $paramJsonFlags);
                        $baseline = $variant->getBaseline();

                        if ($baseline) {
                            $baselineRow = $this->buildRow($baseline, $paramJsonFlags);

                            foreach ($config['baseline_cols'] as $col) {
                                $row->setValue('baseline_' . $col, $baselineRow->getValue($col));
                                $row->getCell($col)->addSecondaryValue(
                                    SecondaryValue::create(
                                        Statistics::percentageDifference($baselineRow->getValue($col), $row->getValue($col)),
                                        'baseline_percentage_diff'
                                    )
                                );
                            }
                        }

                        foreach ($row->getNames() as $columnName) {
                            if (!isset($columnNames[$columnName])) {
                                $columnNames[$columnName] = true;
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
                            $row->setValue('iter', $index);

                            foreach ($iteration->getResults() as $result) {
                                $metrics = $result->getMetrics();

                                // otherwise prefix the metric key with the result key.
                                foreach ($metrics as $key => $value) {

                                    // TODO: this is a hack to add the rev time to the report.
                                    if ($result instanceof TimeResult && $key === 'net') {
                                        $row->setValue($result->getKey() . '_rev', $result->getRevTime($iteration->getVariant()->getRevolutions()));
                                    }

                                    $row->setValue($result->getKey() . '_' . $key, $value);
                                }
                            }
                            $table[] = $row;
                        }
                    }
                }
            }
        }

        // multiple suites may have different column names, for example the
        // number of environment columns may differ. here we iterate over all
        // the rows to ensure they all have all of the columns which have been
        // defined.
        foreach ($table as $row) {
            assert($row instanceof Row);

            foreach (array_keys($columnNames) as $columnName) {
                if (!$row->hasColumn($columnName)) {
                    $row->setValue($columnName, null);
                }
            }
        }

        return $table;
    }

    /**
     * Generate the report DOM document to pass to the report renderer.
     *
     * @param array<array<Row>> $tables
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
        $classMap = array_merge(
            $this->classMap,
            $config['class_map']
        );

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
                assert($row instanceof Row);
                $colsEl = $tableEl->appendElement('cols');

                foreach ($row->getNames() as $colName) {
                    $colEl = $colsEl->appendElement('col');
                    $colEl->setAttribute('name', $colName);

                    // column labels are the column names by default.
                    // the user may override by column name or column index.
                    $colLabel = $colName;

                    if (isset($config['labels'][$colName])) {
                        $colLabel = $config['labels'][$colName];
                    }

                    $colEl->setAttribute('label', $colLabel);
                }

                break;
            }

            if ($breakHash) {
                $tableEl->setAttribute('title', (string)$breakHash);
            }

            $groupEl = $tableEl->appendElement('group');
            $groupEl->setAttribute('name', 'body');

            foreach ($table as $row) {
                assert($row instanceof Row);
                $rowEl = $groupEl->appendElement('row');

                // apply formatter options
                foreach ($row->getFormatParams() as $paramName => $paramValue) {
                    $paramEl = $rowEl->appendElement('formatter-param', $paramValue);
                    $paramEl->setAttribute('name', $paramName);
                }

                foreach ($row->toArray() as $key => $cell) {
                    assert($cell instanceof Cell);
                    $cellEl = $rowEl->appendElement('cell');
                    $cellEl->setAttribute('name', $key);

                    $attributeClasses = isset($classMap[$key]) ? implode(' ', $classMap[$key]) : '';

                    $valueEl = $cellEl->appendElement('value', $cell->getValue());
                    $valueEl->setAttribute('role', ValueRole::ROLE_PRIMARY);
                    $valueEl->setAttribute('class', $attributeClasses);

                    foreach ($cell->getSecondaryValues() as $secondaryValue) {
                        $secondaryValueEl = $cellEl->appendElement('value', $secondaryValue->getValue());
                        $secondaryValueEl->setAttribute('role', $secondaryValue->getRole());
                        $secondaryValueEl->setAttribute('class', 'deviation');
                    }
                }
            }
        }

        return $document;
    }

    /**
     * Return the short name of a fully qualified class name.
     */
    private function getClassShortName(string $fullName): string
    {
        $parts = explode('\\', $fullName);
        end($parts);

        return current($parts);
    }

    /**
     * Recursively resolve a comparison column - find a column name that
     * doesn't already exist by adding and incrementing an index.
     */
    private function resolveCompareColumnName(Row $row, string $name, int $index = 1): string
    {
        if (!$row->hasColumn($name)) {
            return $name;
        }

        $newName = $name . '#' . (string) $index++;

        if (!$row->hasColumn($newName)) {
            return $newName;
        }

        return $this->resolveCompareColumnName($row, $name, $index);
    }

    private function buildRow(Variant $variant, ?int $paramJsonFlags): Row
    {
        $subject = $variant->getSubject();
        $benchmark = $subject->getBenchmark();
        $suite = $benchmark->getSuite();
        $env = $suite->getEnvInformations();

        $row = Row::fromMap([
            'suite' => $suite->getUuid(),
            'tag' => $suite->getTag(),
            'date' => $suite->getDate()->format('Y-m-d'),
            'stime' => $suite->getDate()->format('H:i:s'),
            'benchmark' => $this->getClassShortName($benchmark->getClass()),
            'benchmark_full' => $benchmark->getClass(),
            'subject' => $subject->getName(),
            'groups' => implode(',', $subject->getGroups()),
            'set' => $variant->getParameterSet()->getName(),
            'params' => json_encode($variant->getParameterSet()->getArrayCopy(), $paramJsonFlags),
            'revs' => $variant->getRevolutions(),
            'its' => count($variant->getIterations()),
            'mem_real' => Statistics::mean($variant->getMetricValues(MemoryResult::class, 'real')),
            'mem_final' => Statistics::mean($variant->getMetricValues(MemoryResult::class, 'final')),
            'mem_peak' => Statistics::mean($variant->getMetricValues(MemoryResult::class, 'peak')),
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
        }

        if ($precision = $subject->getOutputTimePrecision()) {
            $formatParams['output_time_precision'] = $precision;
        }

        $row->setFormatParams($formatParams);

        if ($variant->hasErrorStack()) {
            return $row;
        }

        $stats = $variant->getStats()->getStats();
        $stats['best'] = $stats['min'];
        $stats['worst'] = $stats['max'];

        // save on duplication and lazily evaluate the
        // available statistics.
        if (null === $this->statKeys) {
            $this->statKeys = array_keys($stats);
        }

        $row = $row->mergeMap($stats);

        // generate the environment parameters.
        // TODO: should we crash here if an attempt is made to
        //       override a row?  it could happen.
        foreach ($env as $providerName => $information) {
            foreach ($information as $key => $value) {
                $row->setValue($providerName . '_' . $key, $value);
            }
        }

        return $row;
    }
}
