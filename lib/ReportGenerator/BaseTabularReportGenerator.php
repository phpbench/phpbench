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

abstract class BaseTabularReportGenerator implements ReportGenerator
{
    public function configure(OptionsResolver $options)
    {
        $options->setDefaults(array(
            'aggregate_iterations' => false,
            'precision' => 6,
            'time_format' => 'fraction',
            'time' => true,
            'memory' => false,
            'memory_inc' => false,
            'totals' => false,
            'revolutions' => false,
            'footer_sum' => false,
            'footer_avg' => false,
            'footer_min' => false,
            'footer_max' => false,
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

        if ($options['time']) {
            $cols['time'] = array('time');
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
                    $row->set('revs', $iteration->get('time') ? 1000000 / $iteration->get('time') : null, array('revs'));
                }
            }
        }

        if ($options['revolutions']) {
            $cols['revs'] = array('revs');
        }

        $data = $table->getTable();

        if (true === $options['aggregate_iterations']) {
            $data = $data->aggregate(function ($table, $row) use ($cols) {
                $row->remove('iter');
                foreach ($cols as $colName => $groups) {
                    $row->set('iters', count($table->getRows()));
                    $row->set('avg_' . $colName, $table->getColumn($colName)->avg(), $table->getColumn($colName)->getGroups());
                    $row->set('min_' . $colName, $table->getColumn($colName)->min(), $table->getColumn($colName)->getGroups());
                    $row->set('max_' . $colName, $table->getColumn($colName)->max(), $table->getColumn($colName)->getGroups());
                    $row->remove($colName);
                }
            }, array('run'));
        }

        $table = $data->builder();

        foreach (array('sum', 'avg', 'min', 'max') as $function) {
            if (!$options['footer_' . $function]) {
                continue;
            }

            $row = $table->row();
            foreach ($cols as $colName => $groups) {
                $groups[] = 'footer';
                $row->set(' ', '<< ' . $function, array('footer'));
                $row->set($colName, $data->getColumn($colName)->$function(), $groups);
            }
        }

        return $table->getTable();
    }
}
