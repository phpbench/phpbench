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

use PhpBench\BenchCaseCollectionResult;
use PhpBench\BenchReportGenerator;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use PhpBench\BenchAggregateIterationResult;
use PhpBench\BenchIteration;

class ConsoleTableReportGenerator implements BenchReportGenerator
{
    private $output;
    private $precision;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function configure(OptionsResolver $options)
    {
        $options->setDefaults(array(
            'aggregate_iterations' => false,
            'precision' => 8,
        ));

        $options->setAllowedTypes('aggregate_iterations', 'boolean');
        $options->setAllowedTypes('precision', 'int');
    }

    public function generate(BenchCaseCollectionResult $collection, array $options)
    {
        $this->precision = $options['precision'];
        foreach ($collection->getCaseResults() as $case) {
            foreach ($case->getSubjectResults() as $subject) {
                $this->output->writeln(sprintf(
                    '<comment>%s</comment><info>#</info><comment>%s()</comment>: %s',
                    get_class($case->getCase()),
                    $subject->getSubject()->getMethodName(),
                    $subject->getSubject()->getDescription()
                ));

                $table = new Table($this->output);

                if (false === $options['aggregate_iterations']) {
                    $table->setHeaders(array(
                        '#',
                        'Params',
                        'Time',
                    ));
                } else {
                    $table->setHeaders(array(
                        '# Itns.',
                        'Params',
                        'Av.',
                        'Min',
                        'Max',
                        'Total',
                    ));
                }

                $aggregates = $subject->getAggregateIterationResults();

                $rows = array();
                foreach ($aggregates as $aggregate) {
                    foreach ($aggregate->getIterations() as $iteration) {
                        if (false === $options['aggregate_iterations']) {
                            $this->addIteration($iteration, $rows);
                        }
                    }

                    if (true === $options['aggregate_iterations']) {
                        $this->addAggregateIteration($aggregate, $rows);
                    }
                }

                $table->setRows($rows);

                $table->render();
                $this->output->writeln('');
            }
        }
    }

    private function addIteration(BenchIteration $iteration, &$rows)
    {
        $rows[] = array(
            $iteration->getIndex() + 1,
            json_encode($iteration->getParameters()),
            number_format($iteration->getTime(), $this->precision),
        );
    }

    private function addAggregateIteration(BenchAggregateIterationResult $aggregate, &$rows)
    {
        $rows[] = array(
            count($aggregate->getIterations()),
            json_encode($aggregate->getParameters()),
            number_format($aggregate->getAverageTime(), $this->precision),
            number_format($aggregate->getMinTime(), $this->precision),
            number_format($aggregate->getMaxTime(), $this->precision),
            number_format($aggregate->getTotalTime(), $this->precision)
        );
    }
}
