<?php

namespace PhpBench\Report\Generator;

use PhpBench\Report\GeneratorInterface;
use PhpBench\Benchmark\SuiteDocument;
use PhpBench\Registry\Config;
use PhpBench\Dom\Document;
use PhpBench\Math\Statistics;

class HistogramGenerator implements GeneratorInterface
{
    public function generate(SuiteDocument $results, Config $config)
    {
        $document = new Document();
        $reportEl = $document->createRoot('reports');
        $reportEl = $reportEl->appendElement('report');

        foreach ($results->query('//subject') as $subjectEl) {
            foreach ($subjectEl->query('.//variant') as $variantEl) {
                $tableEl = $reportEl->appendElement('table');
                foreach ($variantEl->query('.//iteration') as $iterationEl) {
                    $times[] = $iterationEl->getAttribute('rev-time');
                }
                $histogram = Statistics::histogram($times, $config['bins']);

                foreach ($histogram as $xValue => $frequency) {
                    $rowEl = $tableEl->appendElement('row');
                    $cellEl = $rowEl->appendElement('cell');
                    $cellEl->setAttribute('name', 'time');
                    $cellEl->nodeValue = $xValue;
                    $cellEl = $rowEl->appendElement('cell');
                    $cellEl->setAttribute('name', 'freq');
                    $cellEl->nodeValue = $frequency;
                }
            }
        }

        return $document;
    }

    public function getDefaultConfig()
    {
        return array(
            'bins' => 10,
        );
    }

    public function getSchema()
    {
        return array(
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => array(
                'bins' => array(
                    'type' => 'integer',
                ),
            ),
        );
    }
}
