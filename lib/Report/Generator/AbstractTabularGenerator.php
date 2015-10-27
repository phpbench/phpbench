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
use PhpBench\Console\OutputAwareInterface;
use PhpBench\Dom\Document;
use PhpBench\Report\GeneratorInterface;
use PhpBench\Tabular\Tabular;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractTabularGenerator implements GeneratorInterface, OutputAwareInterface
{
    protected $tabular;
    protected $output;

    public function __construct(Tabular $tabular)
    {
        $this->tabular = $tabular;
    }

    /**
     * {@inheritdoc}
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    protected function doGenerate($definition, SuiteDocument $document, array $config, array $parameters = array())
    {
        if ($config['debug']) {
            $this->output->writeln('<info>Suite XML</info>');
            $this->output->writeln($document->saveXML());
        }

        $tableDom = $this->tabular->tabulate($document, $definition, $parameters);

        if ($config['exclude']) {
            foreach ($config['exclude'] as $cellName) {
                $excludeCells = $tableDom->xpath()->query(sprintf('//cell[@name="%s"]', $cellName));
                foreach ($excludeCells as $excludeCell) {
                    $excludeCell->parentNode->removeChild($excludeCell);
                }
            }
        }

        $reportDom = new Document();
        $reportEl = $reportDom->createRoot('reports');
        $reportEl = $reportEl->appendElement('report');

        if ($config['debug']) {
            $tableDom->formatOutput = true;
            $this->output->writeln('<info>Table XML</info>');
            $this->output->writeln($tableDom->saveXML());
        }

        if ($config['title']) {
            $reportEl->setAttribute('title', $config['title']);
        }

        if ($config['description']) {
            $reportEl->appendElement('description', $config['description']);
        }

        $tableEl = $reportEl->ownerDocument->importNode($tableDom->firstChild, true);
        $reportEl->appendChild($tableEl);

        return $reportEl->ownerDocument;
    }
}
