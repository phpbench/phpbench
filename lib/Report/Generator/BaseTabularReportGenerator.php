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
use PhpBench\Report\Cellular\StepChain;
use DTL\Cellular\Row;

/**
 * This base class generates a table (a data table, not a UI table) with
 * calculated values.
 */
abstract class BaseTabularReportGenerator implements ReportGenerator
{
    /**
     * Available functions.
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

        $options->setBCAllowedValues(array(
            'time_format' => array('integer', 'fraction'),
            'aggregate' => array('none', 'run', 'subject'),
            'sort_dir' => array('asc', 'desc'),
        ));
        $options->setBCAllowedTypes(array(
            'aggregate' => array('string'),
            'precision' => array('int'),
            'cols' => array('array'),
        ));

        $options->setBCNormalizers(array(
            'sort_dir' => function ($resolver, $value) { return strtolower($value); }
        ));
    }

    public function generate(SuiteResult $suite, OutputInterface $output, array $options)
    {
        $this->precision = $options['precision'];

        $workspace = CellularConverter::suiteToWorkspace($suite);

        // TODO: Move this into a step
        if ($options['groups']) {
            $workspace->filter(function (Table $table) use ($options) {
                if (0 === count(array_intersect($table->getAttribute('groups'), $options['groups']))) {
                    return false;
                }

                return true;
            });
        }

        $stepChain = new StepChain();

        $stepChain->add(new RpsStep());

        if ($options['aggregate'] === 'run') {
            $stepChain->add(new AggregateRunStep($this->availableFuncs));
        }

        if ($options['aggregate'] === 'subject') {
            $stepChain->add(new AggregateSubjectStep($this->availableFuncs));
        }

        $stepChain->add(new DeviationStep('time', $this->availableFuncs));

        if ($options['sort']) {
            $stepChain->add(new SortStep($options['sort'], $options['sort_dir']));
        }

        $stepChain->add(new FilterColsStep($options['cols']));

        if (false === empty($options['footer_funcs'])) {
            $stepChain->add(new FooterStep($options['footer_funcs']));
        }

        $stepChain->run($workspace);

        $workspace->each(function (Table $table) {
            $table->align();
        });

        if ($options['cols']) {
            $workspace->each(function (Table $table) use ($options) {
                $table->each(function (Row $row) use ($options) {
                    $row->order($options['cols']);
                });
            });
        }

        $workspace->setAttribute('title', $options['title']);
        $workspace->setAttribute('description', $options['description']);

        return $this->doGenerate($workspace, $output, $options);
    }
}
