<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Report\Generator;

use Symfony\Component\OptionsResolver\OptionsResolver;
use PhpBench\Result\SuiteResult;
use PhpBench\Result\SubjectResult;
use PhpBench\ReportGenerator;
use DTL\Cellular\Table;
use Symfony\Component\Console\Output\OutputInterface;
use DTL\Cellular\Calculator;
use DTL\Cellular\Workspace;
use PhpBench\Result\BenchmarkResult;

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
                'groups' => array(),
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

        $workspace = CellularConverter::suiteToWorkspace($suite);

        if ($options['rps']) {
            $steps[] = new RpsStep();
        }

        if ($options['deviation']) {
            $steps[] = new DeviationStep();
        }

        $filterCols = array_map($this->availableCols, function ($col) use ($options) {
            if ($options[$col]) {
                return true;
            }

            return false;
        });

        if ($filterCols) {
            $steps[] = new FilterColsStep($filterCols);
        }

        $filterFunctions = array_map($this->functions, function ($function) use ($options) {
            foreach ($this->availableCols as $col) {
                if ($options[$function . '_' . $col]) {
                    return true;
                }
            }

            return false;
        });

        if ($options['aggregate_iterations']) {
            $steps[] = new AggregateIterationsStep($filterFunctions);
        }

        $workspace->apply(function (Table $table) {
            $table->align();
        });

        return $this->doGenerate($workspace, $output, $options);
u   }
}
