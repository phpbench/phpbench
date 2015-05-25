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
use DTL\DataTable\Builder\TableBuilder;
use Symfony\Component\Console\Output\OutputInterface;

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
        'sum', 'min', 'max', 'avg',
    );

    /**
     * Columns which are available.
     */
    private $availableCols = array(
        'time' => array('time'),
        'memory' => array('memory'),
        'memory_diff' => array('memory', 'diff'),
        'memory_inc' => array('memory'),
        'memory_diff_inc' => array('memory', 'diff'),
        'rps' => array('rps'),
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
        $table = TableBuilder::create();
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

                $row = $table->row(array('main'));
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

        $data = $table->getTable();
        $table = $data->builder();

        foreach ($table->getRows() as $row) {
            if ($options['deviation']) {
                $avgTime = $data->getColumn('rps')->avg();
                $row->set('deviation', 100 / $avgTime * ($row->get('rps')->value() - $avgTime), array('deviation'));
            }

            foreach (array_keys($this->availableCols) as $colName) {
                if (false === $options[$colName]) {
                    $row->remove($colName);
                }
            }
        }

        $data = $table->getTable();

        $newCols = array();
        if (true === $options['aggregate_iterations']) {
            $functions = $this->functions;
            $data = $data->aggregate(function ($table, $row) use ($cols, &$newCols, $options, $functions) {
                $row->remove('iter');
                foreach ($cols as $colName => $groups) {
                    $row->set('iters', $table->getColumn('iter')->max() + 1);
                    $row->set('revs', $table->getColumn('revs')->sum());

                    foreach ($functions as $function) {
                        if ($options[$function . '_' . $colName]) {
                            $row->set($function . '_' . $colName, $table->getColumn($colName)->$function(), $table->getColumn($colName)->getGroups());
                            $newCols[$function . '_' . $colName] = $table->getColumn($colName)->getGroups();
                        }
                    }

                    $row->remove($colName);
                }
            }, array('run'));
        }

        $table = $data->builder();

        $newCols = empty($newCols) ? $cols : $newCols;

        foreach ($this->functions as $function) {
            if (!$options['footer_' . $function]) {
                continue;
            }
            $row = $table->row();
            $row->set(' ', '<< ' . $function, array('footer'));
            foreach ($newCols as $colName => $groups) {
                $groups[] = 'footer';
                $data->getColumn($colName);
                $row->set($colName, $data->getColumn($colName)->$function(), $groups);
            }
        }

        return $table->getTable();
    }
}
