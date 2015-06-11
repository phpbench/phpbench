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
use PhpBench\Report\Cellular\Step\FooterStep;
use PhpBench\Report\Cellular\Step\FilterColsStep;
use PhpBench\Report\Cellular\Step\AggregateSubjectStep;
use PhpBench\Report\Cellular\Step\SortStep;
use PhpBench\Report\Cellular\Step\AggregateRunStep;

/**
 * This base class generates a table (a data table, not a UI table) with
 * calculated values.
 */
abstract class BaseTabularReportGenerator implements ReportGenerator
{
    /**
     * Available functions
     */
    private $availableFuncs = array('sum', 'mean', 'min', 'max', 'median');

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
                'cols' => array(),
                'precision' => 6,
                'time_format' => 'fraction',
                'sort' => null,
                'sort_dir' => 'asc',
                'groups' => array(),
                'title' => null,
                'description' => null,
            )
        ));

        $functionsValidator = function ($funcs) {
            foreach ($funcs as $function) {
                if (!in_array($function, $this->availableFuncs)) {
                    throw new \InvalidArgumentException(sprintf(
                        'Invalid function: "%s". Valid functions are "%s"',
                        $function,
                        implode('", "', $this->availableFuncs)
                    ));
                }
            }

            return true;
        };

        $options->setAllowedValues('time_format', array('integer', 'fraction'));
        $options->setAllowedValues('aggregate', array('none', 'run', 'subject'));
        $options->setAllowedValues('sort_dir', array('asc', 'desc'));
        $options->setAllowedTypes('aggregate', 'string');
        $options->setAllowedTypes('precision', 'int');
        $options->setNormalizer('sort_dir', function ($resolver, $value) { return strtolower($value); });
    }

    public function generate(SuiteResult $suite, OutputInterface $output, array $options)
    {
        $this->precision = $options['precision'];

        $workspace = CellularConverter::suiteToWorkspace($suite);

        // TODO: Move this into a step
        if ($options['groups']) {
            $workspace = $workspace->filter(function ($table) use ($options) {
                if (0 === count(array_intersect($table->getAttribute('groups'), $options['groups']))) {
                    return false;
                }

                return true;
            });
        }

        $steps = array();

        $steps[] = new RpsStep();

        if ($options['aggregate'] === 'run') {
            $steps[] = new AggregateRunStep($this->availableFuncs);
        }

        if ($options['aggregate'] === 'subject') {
            $steps[] = new AggregateSubjectStep($this->availableFuncs);
        }

        $steps[] = new DeviationStep('time', $this->availableFuncs);

        if ($options['sort']) {
            $steps[] = new SortStep($options['sort'], $options['sort_dir']);
        }

        $steps[] = new FilterColsStep($options['cols']);

        if (false === empty($options['footer_funcs'])) {
            $steps[] = new FooterStep($options['footer_funcs']);
        }

        foreach ($steps as $step) {
            $step->step($workspace);
        }

        // align all the tables (fill in missing columns)
        $workspace->each(function (Table $table) {
            $table->align();
        });

        $workspace->setAttribute('title', $options['title']);
        $workspace->setAttribute('description', $options['description']);

        return $this->doGenerate($workspace, $output, $options);
    }
}
