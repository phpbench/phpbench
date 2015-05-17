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
use DTL\DataTable\Table;
use DTL\DataTable\Builder\TableBuilder;

abstract class BaseTabularReportGenerator implements ReportGenerator
{
    public function configure(OptionsResolver $options)
    {
        $options->setDefaults(array(
            'aggregate_iterations' => false,
            'precision' => 8,
            'time' => true,
            'memory' => false,
            'memory_inc' => false,
            'totals' => false,
            'revolutions' => false,
        ));

        $options->setAllowedTypes('aggregate_iterations', 'bool');
        $options->setAllowedTypes('precision', 'int');
    }

    public function generate(SuiteResult $suite, array $options)
    {
        $this->precision = $options['precision'];

        return $this->doGenerate($suite, $options);
    }

    protected function prepareData(SubjectResult $subject, array $options)
    {
        $table = TableBuilder::create();
        $cols = array();

        if ($options['time']) {
            $cols['time'] = array('float');
        }

        if ($options['memory']) {
            $cols['memory'] = array('memory');
            $cols['memory_diff'] = array('memory');
        }

        if ($options['memory_inc']) {
            $cols['memory_inc'] = array('memory');
            $cols['memory_diff_inc'] = array('memory');
        }

        foreach ($subject->getIterationsResults() as $runIndex => $aggregateResult) {
            foreach ($aggregateResult->getIterationResults() as $index => $iteration) {
                $row = $table->row(array('main'));
                $row->set('run', $runIndex + 1);
                $row->set('iter', $index);
                foreach ($aggregateResult->getParameters() as $paramName => $paramValue) {
                    $row->set($paramName, $paramValue, array('param'));
                }

                foreach ($cols as $colName => $groups) {
                    $row->set($colName, $iteration->get($colName), $groups);
                }

                if ($options['revolutions']) {
                    $row->set('revs', (1 / $iteration->get('time')), array('revs'));
                }
            }
        }


        $data = $table->getTable();

        if (true === $options['aggregate_iterations']) {
            $data = $data->aggregate(function ($table, $row) use ($cols) {
                foreach ($cols as $colName => $groups) {
                    $row->set('iters', count($table->getRows()));
                    $row->set('avg_' . $colName, $table->getColumn($colName)->avg(), $table->getColumn($colName)->getGroups());
                    $row->set('min_' . $colName, $table->getColumn($colName)->min(), $table->getColumn($colName)->getGroups()); 
                    $row->set('max_' . $colName, $table->getColumn($colName)->max(), $table->getColumn($colName)->getGroups()); 
                    $row->remove('iter');
                    $row->remove('time');
                }
            }, array('run'));
        }

        $table = $data->builder();

        if (true === $options['totals']) {
            foreach (array('sum', 'avg', 'min', 'max') as $function) {
                $row = $table->row();
                foreach ($cols as $colName => $groups) {
                    $groups[] = 'footer';
                    $row->set(' ', '<< ' . $function, array('footer'));
                    $row->set($colName, $data->getColumn($colName)->$function(), $groups);
                }
            }
        }

        return $table->getTable();
    }
}
