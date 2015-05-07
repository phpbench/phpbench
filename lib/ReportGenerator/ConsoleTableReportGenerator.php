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
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleTableReportGenerator extends BaseTabularReportGenerator
{
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function doGenerate(BenchCaseCollectionResult $collection, array $options)
    {
        foreach ($collection->getCaseResults() as $case) {
            foreach ($case->getSubjectResults() as $subject) {
                $this->output->writeln(sprintf(
                    '<comment>%s#%s()</comment>: %s',
                    get_class($case->getCase()),
                    $subject->getSubject()->getMethodName(),
                    $subject->getSubject()->getDescription()
                ));

                $data = $this->prepareData($subject, $options);
                $this->renderData($data);

                $this->output->writeln('');
            }
        }
    }

    private function renderData(array $data)
    {
        $table = new Table($this->output);

        $firstRow = reset($data);

        if (!$firstRow) {
            return;
        }

        $table->setHeaders(array_keys($firstRow));

        foreach ($data as $row) {
            $table->addRow($row);
        }

        $table->render();
    }
}
