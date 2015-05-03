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

class ConsoleTableReportGenerator implements BenchReportGenerator
{
    private $output;
    private $expansion = 8;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function generate(BenchCaseCollectionResult $collection)
    {
        foreach ($collection->getCaseResults() as $case) {
            foreach ($case->getSubjectResults() as $subject) {
                $this->output->writeln(sprintf(
                    '<comment>%s</comment><info>#</info><comment>%s()</comment>: %s',
                    get_class($case->getCase()),
                    $subject->getSubject()->getMethodName(),
                    $subject->getSubject()->getDescription()
                ));

                $table = new Table($this->output);
                $table->setHeaders(array(
                    '#',
                    'Params',
                    'Time',
                ));
                $iterations = $subject->getIterations();
                foreach ($iterations as $iteration) {
                    $table->addRow(array(
                        $iteration->getIndex() + 1,
                        json_encode($iteration->getParameters()),
                        number_format($iteration->getTime(), $this->expansion),
                    ));
                }

                $table->render();
                $this->output->writeln('');
            }
        }
    }
}
