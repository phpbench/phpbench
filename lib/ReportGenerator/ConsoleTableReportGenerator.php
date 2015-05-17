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
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

class ConsoleTableReportGenerator extends BaseTabularReportGenerator
{
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
        $this->output->getFormatter()->setStyle(
            'total', new OutputFormatterStyle(null, null, array())
        );
        $this->output->getFormatter()->setStyle(
            'blue', new OutputFormatterStyle('blue', null, array())
        );
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

    private function renderData($data)
    {
        $data->map(function ($cell) {
            return number_format($cell->value(), 2);
        }, array('revs'));

        // format the float cells
        $data->map(function ($cell) {
            $value = $cell->value();
            $value =  number_format($value, $this->precision);
            $value = preg_replace('{^(0.0+)(.+)$}', '\1<blue>\2</blue>', $value);

            return $value;
        }, array('float'));

        // format the memory
        $data->map(function ($cell) {
            $value = $cell->value();
            $prefix = $value < 0 ? '-' : '+';
            return $prefix . number_format($cell->value());
        }, array('memory'));

        // format the footer
        $data->map(function ($cell) {
            return sprintf('<total>%s</total>', $cell->value());
        }, array('footer'));

        $table = new Table($this->output);

        $table->setHeaders($data->getColumnNames());
        foreach ($data->getRows(array('spacer')) as $spacer) {
            $spacer->fill('--');
        }

        foreach ($data->getRows() as $row) {
            $table->addRow($row->toArray());
        }

        $table->render();
   }
}
