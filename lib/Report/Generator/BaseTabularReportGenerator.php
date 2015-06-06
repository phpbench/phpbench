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
use PhpBench\ReportGenerator;
use DTL\Cellular\Table;
use Symfony\Component\Console\Output\OutputInterface;
use PhpBench\Report\Cellular\CellularConverter;
use PhpBench\Report\Cellular\Step\RpsStep;
use PhpBench\Report\Cellular\Step\DeviationStep;
use PhpBench\Report\Cellular\Step\AggregateIterationsStep;
use PhpBench\Report\Cellular\Step\FooterStep;
use PhpBench\Report\Cellular\Step\FilterColsStep;

/**
 * This base class generates a table (a data table, not a UI table) with
 * calculated values.
 */
abstract class BaseTabularReportGenerator implements ReportGenerator
{
    /**
     * Columns which are available.
     */
    private $availableCols = array('time',
        'run',
        'iter',
        'memory',
        'memory_diff',
        'memory_inc',
        'memory_diff_inc',
        'rps',
        'revs',
        'deviation',
    );

    /**
     * See the README file for a detailed description of the options.
     */
    public function configure(OptionsResolver $options)
    {
        $defaults = array();

        $options->setDefaults(array_merge(
            $defaults,
            array(
                'aggregate' => 'none',
                'aggregate_funcs' => array('mean'),
                'footer_funcs' => array(),
                'cols' => $this->availableCols,
                'groups' => array(),
                'precision' => 6,
                'time_format' => 'fraction',
            )
        ));

        $functionsValidator = function ($funcs) {
            $availableFuncs = array('sum', 'mean', 'min', 'max');
            $intersect = array_intersect($funcs, $availableFuncs);
            if (count($intersect) !== count($funcs)) {
                throw new \InvalidArgumentException(sprintf(
                    'Invalid functions: "%s"',
                    implode('", "', array_diff($funcs, $intersect))
                ));
            }

            return true;
        };

        $colsValidator = function ($funcs) {
            $intersect = array_intersect($funcs, $this->availableCols);
            if (count($intersect) !== count($funcs)) {
                throw new \InvalidArgumentException(sprintf(
                    'Invalid columns: "%s"',
                    implode('", "', array_diff($funcs, $intersect))
                ));
            }

            return true;
        };

        $options->setAllowedValues('time_format', array('integer', 'fraction'));
        $options->setAllowedValues('aggregate', array('none', 'iteration'));
        $options->setAllowedValues('aggregate_funcs', $functionsValidator);
        $options->setAllowedValues('footer_funcs', $functionsValidator);
        $options->setAllowedValues('cols', $colsValidator);
        $options->setAllowedTypes('footer_funcs', 'array');
        $options->setAllowedTypes('aggregate', 'string');
        $options->setAllowedTypes('aggregate_funcs', 'array');
        $options->setAllowedTypes('precision', 'int');
    }

    public function generate(SuiteResult $suite, OutputInterface $output, array $options)
    {
        $this->precision = $options['precision'];

        $workspace = CellularConverter::suiteToWorkspace($suite);

        if (in_array('rps', $options['cols'])) {
            $steps[] = new RpsStep();
        }

        if (in_array('deviation', $options['cols'])) {
            $steps[] = new DeviationStep();
        }

        if ($options['aggregate'] === 'iteration') {
            $steps[] = new AggregateIterationsStep($options['aggregate_funcs']);
        }

        if (false === empty($options['footer_funcs'])) {
            $steps[] = new FooterStep($options['footer_funcs']);
        }

        $steps[] = new FilterColsStep(array_diff($this->availableCols, $options['cols']));

        foreach ($steps as $step) {
            $step->step($workspace);
        }

        $workspace->each(function (Table $table) {
            $table->align();
        });

        return $this->doGenerate($workspace, $output, $options);
    }
}
