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

use PhpBench\BenchAggregateIterationResult;
use PhpBench\BenchCaseCollectionResult;
use PhpBench\BenchIteration;
use PhpBench\BenchReportGenerator;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use PhpBench\BenchSubjectResult;

abstract class BaseTabularReportGenerator implements BenchReportGenerator
{
    public function configure(OptionsResolver $options)
    {
        $options->setDefaults(array(
            'aggregate_iterations' => false,
            'precision' => 8,
            'explode_param' => null,
        ));

        $options->setAllowedTypes('aggregate_iterations', 'boolean');
        $options->setAllowedTypes('precision', 'int');
        $options->setAllowedTypes('explode_param', array('null', 'string'));
    }

    public function generate(BenchCaseCollectionResult $collection, array $options)
    {
        $this->precision = $options['precision'];
        return $this->doGenerate($collection, $options);
    }

    protected function prepareData(BenchSubjectResult $subject, array $options)
    {
        $data = array();

        foreach ($subject->getAggregateIterationResults() as $runIndex => $aggregateResult) {
            foreach ($aggregateResult->getIterations() as $iteration) {
                $row = array();
                $row['run'] = $runIndex + 1;
                $row['iter'] = $iteration->getIndex() + 1;
                $row['iters'] = $aggregateResult->getIterationCount();
                $row['parameters'] = $iteration->getParameters();
                foreach ($iteration->getParameters() as $paramName => $paramValue) {
                    $row[$paramName] = $paramValue;
                }
                $row['time'] = number_format(
                    $options['aggregate_iterations'] ? $aggregateResult->getAverageTime() : $iteration->getTime(),
                    $this->precision
                );
                $row['min_time'] = number_format($aggregateResult->getMinTime(), $this->precision);
                $row['max_time'] = number_format($aggregateResult->getMaxTime(), $this->precision);
                $row['total_time'] = number_format($aggregateResult->getTotalTime(), $this->precision);

                $data[] = $row;
            }
        }

        if ($options['aggregate_iterations']) {
            $data = $this->aggregateIterations($data);
        }

        if ($options['explode_param']) {
            $data = $this->explodeParam($data, $options['explode_param']);
        }

        foreach ($data as &$row) {
            unset($row['parameters']);
            unset($row['run']);

            if (!$options['aggregate_iterations'] || $options['explode_param']) {
                unset($row['min_time']);
                unset($row['max_time']);
                unset($row['total_time']);
            }

            if ($options['aggregate_iterations']) {
                unset($row['iter']);
            } else {
                unset($row['iters']);
            }
        }

        return $data;
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
                        $newRow[$param . ':' .$extractName] = $time;
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

