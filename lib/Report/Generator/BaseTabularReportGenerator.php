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
use PhpBench\Report\Cellular\Step\ReplaceDescriptionTokensStep;
use PhpBench\Report\Cellular\Step\AggregateStep;
use PhpBench\Report\Cellular\Step\ExplodeStep;

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
        $options->setDefaults(
            array(
                'aggregate' => '',
                'explode' => '',
                'cols' => array(),
                'precision' => 6,
                'time_format' => 'fraction',
                'sort' => array(),
                'groups' => array(),
                'title' => null,
                'description' => null,
                'footer' => array(),
            )
        );

        $options->setBCAllowedValues(array(
            'time_format' => array('integer', 'fraction'),
        ));
        $options->setBCAllowedTypes(array(
            'aggregate' => array('string'),
            'precision' => array('int'),
            'cols' => array('array'),
            'sort' => array('array'),
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
        $stepChain->add(new ReplaceDescriptionTokensStep());

        if ($options['aggregate']) {
            $stepChain->add(new SortStep(array($options['aggregate'])));
            $stepChain->add(new AggregateStep('mean', array($options['aggregate'])));
        }

        if ($options['explode']) {
            $stepChain->add(new SortStep(array($options['explode'])));
            $stepChain->add(new ExplodeStep(array($options['explode'])));
        }

        $stepChain->add(new DeviationStep('time', $this->availableFuncs));

        if ($options['sort']) {
            $stepChain->add(new SortStep($options['sort']));
        }

        $stepChain->add(new FilterColsStep($options['cols']));

        if (false === empty($options['footer'])) {
            $stepChain->add(new FooterStep($options['footer']));
            // Add the "label" column
            $options['cols'][] = ' ';
        }

        $stepChain->run($workspace);

        if ($options['cols']) {
            $workspace->each(function (Table $table) use ($options) {
                $table->each(function (Row $row) use ($options) {
                    $row->order($options['cols']);
                });
            });
        }

        $workspace->each(function (Table $table) {
            $table->align();
        });

        $workspace->setAttribute('title', $options['title']);
        $workspace->setAttribute('description', $options['description']);

        return $this->doGenerate($workspace, $output, $options);
    }
}
