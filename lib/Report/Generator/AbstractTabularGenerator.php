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
use PhpBench\Registry\Config;
use PhpBench\Report\GeneratorInterface;
use PhpBench\Tabular\Definition\Loader;
use PhpBench\Tabular\Tabular;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractTabularGenerator implements GeneratorInterface, OutputAwareInterface
{
    protected $tabular;
    protected $output;
    protected $definitionLoader;

    public function __construct(Tabular $tabular, Loader $definitionLoader)
    {
        $this->tabular = $tabular;
        $this->definitionLoader = $definitionLoader;
    }

    /**
     * {@inheritdoc}
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    protected function doGenerate($definition, SuiteDocument $document, Config $config, array $parameters = array())
    {
        if ($config['debug']) {
            $this->output->writeln('<info>Suite XML</info>');
            $this->output->writeln($document->saveXML());
        }

        $definition = $this->definitionLoader->load($definition);
        if (isset($config['pretty_params']) && true === $config['pretty_params']) {
            $definition['classes']['params'] = array(
                array('json_format', array()),
            );
        }

        if (array_key_exists('formatting', $config) && $config['formatting'] === false) {
            foreach ($definition['classes'] as &$class) {
                $class = array();
            }
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
        $reportEl->setAttribute('name', $config->getName());
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
