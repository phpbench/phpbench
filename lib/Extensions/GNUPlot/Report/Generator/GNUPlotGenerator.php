<?php

namespace PhpBench\Extensions\GNUPlot\Report\Generator;

use PhpBench\Report\GeneratorInterface;
use PhpBench\Benchmark\SuiteDocument;
use PhpBench\Dom\Document;

class GNUPlotGenerator implements GeneratorInterface
{
    /**
     * {@inheritDoc}
     */
    public function generate(SuiteDocument $document, array $config)
    {
        if ($config['type'] == 'gnuplot') {
            $script = $this->generateLineScript($document);
        } else {
            $script = $this->generateHistogramScript($document);
        }

        $reportDom = new Document();
        $reportEl = $reportDom->createRoot('reports');
        $reportEl = $reportEl->appendElement('report');
        $scriptEl = $reportEl->appendElement('script');

        $scriptEl->nodeValue = $script;

        return $reportDom;
    }

    public function generateHistogramScript(SuiteDocument $document)
    {
        foreach ($document->query('.//subject') as $subjectEl) {
            $dataSet = array();
            foreach ($subjectEl->query('.//iteration') as $iterationEl) {
                $dataSet[] = $iterationEl->getAttribute('z-value');
            }
            $dataSets[] = $dataSet;
        }

        $script[] = 'set style fill solid';
        $script[] = 'set yzeroaxis';
        $script[] = 'set boxwidth 0.05 absolute';
        $script[] = 'width = 0.1;';
        $script[] = 'set yrange [0:*]';
        $script[] = 'set xrange [-3:3]';
        $script[] = 'bin(x) = width * ( floor( x / width ) + 0.5)';
        $script[] = 'set xlabel "Z-Score"';
        $script[] = 'set ylabel "Aggregate time"';
        $script[] = 'plot "-" using (bin($1)):(1) smooth frequency with boxes title "Freq"';

        foreach ($dataSets as $dataSet) {
            foreach ($dataSet as $data) {
                $script[] = $data;
            }
        }

        return implode("\n", $script);
    }

    public function generateLineScript(SuiteDocument $document)
    {
        $dataSets = array();
        $withLines = array();
        foreach ($document->query('.//subject') as $subjectEl) {
            $dataSet = array();
            foreach ($subjectEl->query('.//iteration') as $iterationEl) {
                $dataSet[] = $iterationEl->getAttribute('time');
            }
            $benchmark = $subjectEl->evaluate('string(ancestor::benchmark/@class)');
            $dataSets[] = $dataSet;
            $withLines[] = sprintf('"-" with lines title "%s::%s"', $benchmark, $subjectEl->getAttribute('name'));
        }

        $script[] = 'set ylabel "Time μs/r"';
        $script[] = 'set xlabel "Iteration #"';
        $script[] = sprintf('plot %s', implode(', ', $withLines));

        foreach ($dataSets as $dataSet) {
            foreach ($dataSet as $data) {
                $script[] = $data;
            }
            $script[] = 'e';
        }

        return implode("\n", $script);
    }

    private function appendData(array $dataSets, &$script)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultReports()
    {
        return array(
            'gnuplot' => array(),
            'gnuhist' => array(
                'type' => 'gnuhist',
            )
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getSchema()
    {
        return array(
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => array(
                'type' => array(
                    'enum' => array(
                        'gnuplot',
                        'gnuhist',
                    ),
                ),
            ),
        );
    }

    /***
     * {@inheritDoc}
     */
    public function getDefaultConfig()
    {
        return array(
            'type' => 'gnuplot',
        );
    }
}
