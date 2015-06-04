<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\ReportGenerator;

use Symfony\Component\OptionsResolver\OptionsResolver;
use PhpBench\Result\SuiteResult;
use PhpBench\Result\SubjectResult;
use PhpBench\ReportGenerator;
use DTL\Cellular\Table;
use Symfony\Component\Console\Output\OutputInterface;
use DTL\Cellular\Calculator;

/**
 * This base class generates a table (a data table, not a UI table) with
 * calculated values.
 */
abstract class BaseTabularReportGenerator implements ReportGenerator
{
    /**
     * Available aggregate functions.
     */
    private $functions = array(
        'sum', 'min', 'max', 'mean',
    );

    /**
     * Columns which are available.
     */
    private $availableCols = array(
        'time' => array('main', 'time'),
        'memory' => array('main', 'memory'),
        'memory_diff' => array('main', 'memory', 'diff'),
        'memory_inc' => array('main', 'memory'),
        'memory_diff_inc' => array('memory', 'diff'),
        'rps' => array('main', 'rps'),
        'revs' => array('revs'),
    );

    /**
     * See the README file for a detailed description of the options.
     */
    public function configure(OptionsResolver $options)
    {
        $defaults = array();

        foreach (array_keys($this->availableCols) as $colName) {
            $defaults[$colName] = false;
        }

        foreach ($this->functions as $function) {
            foreach (array_keys($this->availableCols) as $colName) {
                $defaults[$function . '_' . $colName] = false;
            }
            $defaults['footer_' . $function] = false;
        }

        $options->setDefaults(array_merge(
            $defaults,
            array(
                'aggregate_iterations' => false,
                'precision' => 6,
                'time_format' => 'fraction',
                'time' => true,
                'revs' => true,
                'rps' => true,
                'memory_diff' => true,
                'deviation' => true,
            )
        ));

        $options->setAllowedValues('time_format', array('integer', 'fraction'));
        $options->setAllowedTypes('aggregate_iterations', 'bool');
        $options->setAllowedTypes('precision', 'int');
    }

    public function generate(SuiteResult $suite, OutputInterface $output, array $options)
    {
        $this->precision = $options['precision'];

        return $this->doGenerate($suite, $output, $options);
    }

    protected function prepareData(SubjectResult $subject, array $options)
    {
        $table = Table::create();
        $cols = array();

        foreach ($this->availableCols as $colName => $colGroups) {
            if ($options[$colName]) {
                $cols[$colName] = $colGroups;
            }
        }

        foreach ($subject->getIterationsResults() as $runIndex => $aggregateResult) {
            foreach ($aggregateResult->getIterationResults() as $iteration) {
                $stats = $iteration->getStatistics();
                $stats['rps'] = $stats['time'] ? (1000000 / $stats['time']) * $stats['revs'] : null;

                $row = $table->createAndAddRow(array('main'));
                $row->set('run', $runIndex + 1);
                $row->set('iter', $stats['index'] + 1);
                $row->set('revs', $stats['revs']);

                foreach ($aggregateResult->getParameters() as $paramName => $paramValue) {
                    $row->set($paramName, $paramValue, array('param'));
                }

                foreach ($cols as $colName => $groups) {
                    $row->set($colName, $stats[$colName], $groups);
                }
            }
        }

        foreach ($table->getRows() as $row) {
            if ($options['deviation']) {
                $meanRps = Calculator::mean($table->getColumn('rps'));
                $row->set('deviation', Calculator::deviation($meanRps, $row->getCell('rps')), array('deviation', 'main'));
            }

            foreach (array_keys($this->availableCols) as $colName) {
                if (false === $options[$colName]) {
                    $row->remove($colName);
                }
            }
        }

        $newCols = array();
        if (true === $options['aggregate_iterations']) {
            $functions = $this->functions;
            $table = $table
                ->partition(function ($row) {
                    return $row['run']->getValue();
                })
                ->fork(function ($table, $newTable) use ($cols, &$newCols, $options, $functions) {
                    if (!$table->first()) {
                        continue;
                    }
                    $row = clone $table->first();
                    $row->set('run', Calculator::mean($table->getColumn('run')));
                    $row->set('iters', $table->count());
                    $row->set('revs', Calculator::sum($table->getColumn('revs')));
                    $row->remove('iter');
                    $row->remove('time');
                    $row->remove('rps');
                    $row->remove('memory_diff');
                    $row->set('min_deviation', Calculator::min($table->getColumn('deviation')), array('deviation'));
                    $row->set('max_deviation', Calculator::max($table->getColumn('deviation')), array('deviation'));
                    $row->remove('deviation');
                    foreach ($cols as $colName => $groups) {
                        foreach ($functions as $function) {
                            if ($options[$function . '_' . $colName]) {
                                $row->set($function . '_' . $colName, Calculator::$function($table->getColumn($colName)), $table->getColumn($colName)->getGroups());
                                $newCols[$function . '_' . $colName] = $table->getColumn($colName)->getGroups();
                            }
                        }
                    }
                    $newTable->addRow($row);
                });
        }

        $newCols = empty($newCols) ? $cols : $newCols;

        foreach ($this->functions as $function) {
            if (!$options['footer_' . $function]) {
                continue;
            }
            $row = $table->createAndAddRow();
            $row->set(' ', '<< ' . $function, array('footer'));
            foreach ($newCols as $colName => $groups) {
                $groups[] = 'footer';
                $row->set(
                    $colName, 
                    Calculator::$function($table->getColumn($colName)->getValues(array('main'))),
                    $table->getColumn($colName)->getGroups()
                );
            }
        }

        $table->align();

        return $table;
    }
}
