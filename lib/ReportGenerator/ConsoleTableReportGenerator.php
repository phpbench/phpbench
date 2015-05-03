<?php

namespace PhpBench\ReportGenerator;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use PhpBench\BenchReportGenerator;
use PhpBench\BenchCaseCollectionResult;

class ConsoleTableReportGenerator implements BenchReportGenerator
{
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function generate(BenchCaseCollectionResult $collection)
    {
        foreach ($collection->getCaseResults() as $case) {
            foreach ($case->getSubjectResults() as $subject) {
                $this->output->writeln(sprintf(
                    '<comment>%s</comment>#<comment>%s</comment>: %s', 
                    get_class($case->getCase()),
                    $subject->getSubject()->getMethodName(),
                    $subject->getSubject()->getDescription()
                ));

                $table = new Table($this->output);;
                $table->setHeaders(array(
                    'I. #',
                    'Params',
                    'Time',
                ));
                $totalTime = 0;
                $iterations = $subject->getIterations();
                foreach ($iterations as $iteration) {
                    $totalTime+= $iteration->getTime();
                    $table->addRow(array(
                        $iteration->getIndex(),
                        json_encode(array()),
                        number_format($iteration->getTime(), 6)
                    ));
                }

                $averageTime = $totalTime / count($iterations);
                $table->render();
                $this->output->writeln('');
            }
        }

    }
}
