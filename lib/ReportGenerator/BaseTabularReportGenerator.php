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

abstract class BaseTabularReportGenerator implements ReportGenerator
{
    public function configure(OptionsResolver $options)
    {
        $options->setDefaults(array(
            'aggregate_iterations' => false,
            'precision' => 8,
            'explode_param' => null,
            'memory' => false,
            'memory_inc' => false,
        ));

        $options->setAllowedTypes('aggregate_iterations', 'bool');
        $options->setAllowedTypes('precision', 'int');
        $options->setAllowedTypes('explode_param', array('null', 'string'));
    }

    public function generate(SuiteResult $suite, array $options)
    {
        $this->precision = $options['precision'];

        return $this->doGenerate($suite, $options);
    }

    protected function prepareData(SubjectResult $subject, array $options)
    {
        $table = Table::createBuilder();

        foreach ($subject->getIterationsResults() as $runIndex => $aggregateResult) {
            foreach ($aggregateResult->getIterationResults() as $index => $iteration) {
                $row = $table->row(array('main'));
                $row->set('run', $runIndex + 1);
                $row->set('iter', $index);
                foreach ($aggregateResult->getParameters() as $paramName => $paramValue) {
                    $row->set($paramName, $paramValue, array('param'));
                }
                $row->set('time', $iteration->get('time'), array('time', 'float'));
                $row->set('memory', $iteration->get('subject_memory_total'), array('memory'));
                $row->set('memory_diff', $iteration->get('subject_memory_diff'), array('memory'));
                $row->set('memory_inc', $iteration->get('memory_inclusive'), array('memory'));
                $row->set('memory_diff_inc', $iteration->get('memory_diff_inclusive'), array('memory'));
            }
        }

        $data = $table->getTable();
        $table = $data->builder();

        foreach (array('sum', 'avg', 'min', 'max') as $function) {
            $row = $table->row();
            foreach (array(
                'time' => array('float'),
                'memory' => array('memory'),
                'memory_diff' => array('memory'),
                'memory_inc' => array('memory'),
                'memory_diff_inc' => array('memory'),
            ) as $column => $groups) {
                $groups[] = 'footer';
                $row->set(' ', '<< ' . $function, array('footer'));
                $row->set($column, $data->getColumn($column)->$function(), $groups);
            }
        }

        return $table->getTable();
    }

    private function aggregateIterations($data)
    {
        $iterations = array();
        foreach ($data as $row) {
            $iterations[$row['run']] = $row;
        }

        return $iterations;
    }

    private function explodeParam($data, $param)
    {
        $xseries = array();
        $seenParams = array();
        foreach ($data as $index => $row) {
            if (!isset($row[$param])) {
                continue;
            }
            $paramValue = $row[$param];
            if (!isset($xseries[$paramValue])) {
                $xseries[$paramValue] = array();
            }
            $parameters = $row['parameters'];
            unset($parameters[$param]);

            $paramHash = serialize($parameters);
            if (isset($seenParams[$paramHash])) {
                unset($data[$index]);
            }
            $seenParams[$paramHash] = true;
            $xseries[$paramValue][] = $row['time'];
        }

        $data = array_values($data);

        foreach ($data as $i => $row) {
            if (!isset($row[$param])) {
                continue;
            }

            $paramValue = $row[$param];
            $newRow = array();
            foreach ($row as $key => $value) {
                if ($key === $param) {
                    continue;
                }
                if ($key === 'time') {
                    foreach ($xseries as $extractName => $extractParam) {
                        $time = $extractParam[$i];
                        $newRow[$param . '-' . $extractName] = $time;
                    }
                } else {
                    $newRow[$key] = $value;
                }
            }

            $data[$i] = $newRow;
        }

        return $data;
    }
}
