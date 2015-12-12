<?php

namespace PhpBench\Extensions\RProject\Report\Generator;

use PhpBench\Report\GeneratorInterface;
use PhpBench\Benchmark\SuiteDocument;
use PhpBench\Dom\Document;

class RScriptGenerator implements GeneratorInterface
{
    /**
     * {@inheritDoc}
     */
    public function generate(SuiteDocument $document, array $config)
    {
        $script = $this->generateScript($document);

        $reportDom = new Document();
        $reportEl = $reportDom->createRoot('reports');
        $reportEl = $reportEl->appendElement('report');
        $scriptEl = $reportEl->appendElement('script');

        $scriptEl->nodeValue = $script;

        return $reportDom;
    }

    public function generateScript(SuiteDocument $document)
    {
        $dataSets = array();
        foreach ($document->query('.//subject') as $subjectEl) {
            $dataSet = array();
            foreach ($subjectEl->query('.//iteration') as $iterationEl) {
                $dataSet[] = $iterationEl->getAttribute('time');
            }
            $dataSets[$subjectEl->getAttribute('name')] = $dataSet;
        }


        $script = array();
        $script[] = '#!/usr/bin/R';
        $script[] = 'library(ggplot2)';
        $script[] = 'library(reshape2)';
        $script[] = 'png("plot.png")';

        $colors = array('red', 'green', 'blue', 'purple', 'orange');
        $colorMod = count($colors);

        $index = 0;
        foreach ($dataSets as $dataName => $dataSet) {
            $script[] = sprintf('%s <- c(%s)', $dataName, implode(', ', $dataSet));
        }

        foreach (array_keys($dataSets) as $dataName) {
            $script[] = sprintf(
                '%s(%s, type="o", col="%s")',
                $index === 0 ? 'plot' : 'lines', 
                $dataName,
                $colors[$index % $colorMod]
            );
            $index++;
        }

        return implode("\n", $script);
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultReports()
    {
        return array(
            'rscript' => array()
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getSchema()
    {
        return array();
    }

    /***
     * {@inheritDoc}
     */
    public function getDefaultConfig()
    {
        return array();
    }
}
