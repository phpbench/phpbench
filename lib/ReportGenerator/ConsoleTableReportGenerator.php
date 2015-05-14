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

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;
use PhpBench\Result\SuiteResult;

class ConsoleTableReportGenerator extends BaseTabularReportGenerator
{
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function doGenerate(SuiteResult $suite, array $options)
    {
        foreach ($suite->getBenchmarkResults() as $benchmark) {
            foreach ($benchmark->getSubjectResults() as $subject) {
                $this->output->writeln(sprintf(
                    '<comment>%s#%s()</comment>: %s',
                    $benchmark->getClass(),
                    $subject->getName(),
                    $subject->getDescription()
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
