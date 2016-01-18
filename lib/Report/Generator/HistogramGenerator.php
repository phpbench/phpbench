<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Report\Generator;

use PhpBench\Benchmark\SuiteDocument;
use PhpBench\Dom\Document;
use PhpBench\Dom\functions;
use PhpBench\Math\Kde;
use PhpBench\Math\Statistics;
use PhpBench\Registry\Config;
use PhpBench\Report\GeneratorInterface;

/**
 * Experimental histogram generator.
 *
 * Generates a basic histogram. This generator is currently
 * more for general interest, and should not be used for any
 * serious purpose at this stage.
 */
class HistogramGenerator implements GeneratorInterface
{
    public function generate(SuiteDocument $results, Config $config)
    {
        $document = new Document();
        $reportEl = $document->createRoot('reports');
        $reportEl->setAttribute('name', $config->getName());
        $reportEl = $reportEl->appendElement('report');
        $descriptionEl = $reportEl->appendElement('description');
        $descriptionEl->nodeValue = <<<EOT
Warning: The histogram report is experimental, it may change or be removed without warning in
future versions of PHPBench.
EOT;

        $tableEl = $reportEl->appendElement('table');

        foreach ($results->query('//subject') as $subjectEl) {
            foreach ($subjectEl->query('.//variant') as $variantEl) {
                $times = array();
                foreach ($variantEl->query('.//iteration') as $iterationEl) {
                    $times[] = $iterationEl->getAttribute('rev-time');
                }

                if (count($times) > 1) {
                    $histogram = Statistics::histogram($times, $config['bins']);
                    $kde = new Kde($times);
                    $kdeX = Statistics::linspace(min($times), max($times), $config['bins'] + 1);
                    $kdeY = $kde->evaluate($kdeX);
                } else {
                    $histogram = array((string) current($times) => 1);
                    $kdeY = array(null);
                }

                $counter = 0;
                foreach ($histogram as $xValue => $frequency) {
                    $kdeVal = $kdeY[$counter++];
                    $rowEl = $tableEl->appendElement('row');
                    $cellEl = $rowEl->appendElement('cell');
                    $cellEl->setAttribute('name', 'benchmark');
                    $cellEl->nodeValue = functions\class_name($subjectEl->evaluate('string(ancestor::benchmark/@class)'));
                    $cellEl = $rowEl->appendElement('cell');
                    $cellEl->setAttribute('name', 'subject');
                    $cellEl->nodeValue = $subjectEl->evaluate('string(./@name)');
                    $cellEl = $rowEl->appendElement('cell');
                    $cellEl->setAttribute('name', 'index');
                    $cellEl->nodeValue = $counter;
                    $cellEl = $rowEl->appendElement('cell');
                    $cellEl->setAttribute('name', 'time');
                    $cellEl->nodeValue = $xValue;
                    $cellEl = $rowEl->appendElement('cell');
                    $cellEl->setAttribute('name', 'freq');
                    $cellEl->nodeValue = $frequency;
                    $cellEl = $rowEl->appendElement('cell');
                    $cellEl->setAttribute('name', 'kde');
                    $cellEl->nodeValue = $kdeVal;
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
