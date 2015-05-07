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
use Symfony\Component\OptionsResolver\OptionsResolver;
use PhpBench\BenchPhp;

class XmlTableReportGenerator extends BaseTabularReportGenerator
{
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function configure(OptionsResolver $options)
    {
        parent::configure($options);
        $options->setDefaults(array(
            'outfile' => 'bench.xml',
            'stdout' => false,
            'format' => false,
        ));
    }

    public function doGenerate(BenchCaseCollectionResult $collection, array $options)
    {
        $dom = new \DOMDocument('1.0');
        $rootEl = $dom->createElement('bench');
        $rootEl->setAttribute('version', BenchPhp::VERSION);
        $dom->appendChild($rootEl);
        $outfile = $options['outfile'];

        if ($options['format']) {
            $dom->formatOutput = true;
        }

        foreach ($collection->getCaseResults() as $case) {
            $caseEl = $dom->createElement('case');
            $caseEl->setAttribute('class', get_class($case->getCase()));
            $rootEl->appendChild($caseEl);

            foreach ($case->getSubjectResults() as $subject) {
                $data = $this->prepareData($subject, $options);
                $subjectEl = $dom->createElement('subject');
                $subjectEl->setAttribute('name', $subject->getSubject()->getMethodName());
                $subjectEl->setAttribute('description', $subject->getSubject()->getDescription());
                $caseEl->appendChild($subjectEl);

                foreach ($data as $iteration) {
                    $iterationEl = $dom->createElement('iteration');
                    foreach ($iteration as $key => $value) {
                        $iterationEl->setAttribute($key, $value);
                    }
                    $subjectEl->appendChild($iterationEl);
                }

            }
        }

        $this->output->writeln(sprintf('<info>Writing XML file to</info>: %s', $outfile));
        file_put_contents($outfile, $dom->saveXml());

        if ($options['stdout']) {
            $this->output->write($dom->saveXml());
        }
    }
}
