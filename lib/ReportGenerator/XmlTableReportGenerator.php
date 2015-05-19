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

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use PhpBench\Result\SuiteResult;
use PhpBench\PhpBench;

class XmlTableReportGenerator extends BaseTabularReportGenerator
{
    public function configure(OptionsResolver $options)
    {
        parent::configure($options);
        $options->setDefaults(array(
            'outfile' => 'bench.xml',
            'stdout' => false,
            'format' => false,
        ));
    }

    public function doGenerate(SuiteResult $suite, OutputInterface $output, array $options)
    {
        $dom = new \DOMDocument('1.0');
        $rootEl = $dom->createElement('bench');
        $rootEl->setAttribute('version', PhpBench::VERSION);
        $dom->appendChild($rootEl);
        $outfile = $options['outfile'];

        if ($options['format']) {
            $dom->formatOutput = true;
        }

        foreach ($suite->getBenchmarkResults() as $benchmark) {
            $benchmarkEl = $dom->createElement('benchmark');
            $benchmarkEl->setAttribute('class', $benchmark->getClass());
            $rootEl->appendChild($benchmarkEl);

            foreach ($benchmark->getSubjectResults() as $subject) {
                $data = $this->prepareData($subject, $options);
                $subjectEl = $dom->createElement('subject');
                $subjectEl->setAttribute('name', $subject->getName());
                $subjectEl->setAttribute('description', $subject->getDescription());
                $benchmarkEl->appendChild($subjectEl);

                foreach ($data as $iteration) {
                    $iterationEl = $dom->createElement('iteration');
                    foreach ($iteration as $key => $value) {
                        $iterationEl->setAttribute($key, $value);
                    }
                    $subjectEl->appendChild($iterationEl);
                }
            }
        }

        $output->writeln(sprintf('<info>Writing XML file to</info>: %s', $outfile));
        file_put_contents($outfile, $dom->saveXml());

        if ($options['stdout']) {
            $this->output->write($dom->saveXml());
        }
    }
}
