<?php

namespace PhpBench\Report\Generator;

use PhpBench\Console\OutputAware;
use PhpBench\Result\Dumper\XmlDumper;
use Symfony\Component\Console\Helper\Table;
use PhpBench\ReportGenerator;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Console\Output\OutputInterface;
use PhpBench\Result\SuiteResult;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use PhpBench\Report\Dom\PhpBenchXpath;
use PhpBench\Report\Util;
use PhpBench\Report\Tool\Sort;
use PhpBench\Report\Tool\Formatter;

class ConsoleTableGenerator implements OutputAware, ReportGenerator
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var XmlDumper
     */
    private $xmlDumper;

    /**
     * @var Formatter
     */
    private $formatter;

    public function __construct(XmlDumper $xmlDumper = null, Formatter $formatter = null)
    {
        $this->xmlDumper = $xmlDumper ? : new XmlDumper();
        $this->formatter = $formatter ? : new Formatter();
    }

    public function configure(OptionsResolver $options)
    {
        $options->setDefaults(array(
            'title' => null,
            'description' => null,
            'selector' => '//iteration',
            'headers' => array('Subject', 'Group', 'Description', 'Revs', 'Iter.', 'Time', 'Rps', 'Deviation'),
            'cells' => array(
                'subject' => 'string(../../@name)',
                'group' => 'string(../../group/@name)',
                'description' => 'string(../../@description)',
                'revs' => 'number(.//@revs)',
                'iter' => 'number(.//@index)',
                'time' => 'number(.//@time)',
                'rps' => '(1000000 div number(.//@time)) * number(.//@revs)',
                'deviation' => 'php:bench(\'deviation\', php:bench(\'min\', {selector}/@time), number(./@time))',
            ),
            'post-process' => array(),
            'format' => array(
                'revs' => '!number',
                'rps' => array('!number', '%s<comment>rps</comment>'),
                'time' => array('!number', '%s<comment>μs</comment>'),
                'deviation' => array('%.2f', '!balance', '%s<comment>%%</comment>'),
            ),
            'sort' => array(),
            'exclude' => array(),
        ));
    }

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
        $this->configureFormatters($output->getFormatter());
    }

    public function generate(SuiteResult $suite, array $config)
    {
        if (null !== $config['title']) {
            $this->output->writeln(sprintf('<title>%s</title> <comment>%s</comment>', $config['title'], $config['selector']));
        }

        if (null !== $config['description']) {
            $this->output->writeln(sprintf('<description>%s</description>', $config['description']));
        }

        $dom = $this->xmlDumper->dump($suite);

        $tableDom = new \DOMDocument(1.0);

        $this->transformToTableDom($dom, $tableDom, $config);
        $rows = $this->postProcess($tableDom, $config);

        if (!empty($config['sort'])) {
            Sort::sortRows($rows, $config['sort']);
        }

        foreach ($rows as &$row) {
            foreach ($row as $colName => &$value) {
                if (isset($config['format'][$colName])) {
                    $value = $this->formatter->format($value, $config['format'][$colName]);
                }
            }
        }

        $table = new Table($this->output);
        $table->setHeaders($config['headers']);
        $table->setRows($rows);
        $table->render();
        $this->output->writeln('');
    }

    /**
     * Transform the suite result DOM into a DOM representing a table.
     *
     * @param \DOMDocument $resultDom
     * @param \DOMDocument $tableDom
     * @param array $config
     */
    private function transformToTableDom(\DOMDocument $resultDom, \DOMDocument $tableDom, array $config)
    {
        $tableEl = $tableDom->createElement('table');
        $tableDom->appendChild($tableEl);

        $xpath = new PhpBenchXpath($resultDom);
        foreach ($xpath->query($config['selector']) as $rowEl) {
            $tableRowEl = $tableDom->createElement('row');
            $tableEl->appendChild($tableRowEl);

            foreach ($config['cells'] as $colName => $cellExpr) {
                $tableCellEl = $tableDom->createElement('cell');
                $tableRowEl->appendChild($tableCellEl);

                if (in_array($colName, $config['post-process'])) {
                    $value = null;
                } else {
                    $cellExpr = str_replace('{selector}', $config['selector'], $cellExpr);
                    $value = $xpath->evaluate($cellExpr, $rowEl);
                    $this->validateXpathResult($cellExpr, $value);
                }

                $tableCellEl->setAttribute('name', $colName);
                $tableCellEl->nodeValue = $value;
            }
        }
    }

    /**
     * Evaluate XPath expressions defined in the "cells" configuration key that
     * are nominted to be applied to the "table" DOM instead of the "suite
     * result" DOM. This is effectively a compiler pass.
     *
     * NOTE: If further compiler passes are ever needed then the
     *       ['post-process'] configuration key could accept an array instead of any
     *       one of the cell names. The array would then contain one element per
     *       compiler pass.
     *
     * @param \DOMDocument $tableDom 
     * @param array $config
     */
    private function postProcess(\DOMDocument $tableDom, $config)
    {
        $rows = array();
        $tableXpath = new PhpBenchXpath($tableDom);
        foreach ($tableXpath->query('//row') as $rowEl) {
            foreach ($config['post-process'] as $cellName) {
                $expression = './cell[@name="' . $cellName .'"]';
                $cellEls = $tableXpath->query($expression, $rowEl);

                if (false === $cellEls) {
                    throw new \InvalidArgumentException(sprintf(
                        'Could not find cell with name "%s" using expression "%s" when post processing table',
                        $cellName,
                        $expression
                    ));
                }

                $cellEl = $cellEls->item(0);
                $cellExpr = $config['cells'][$cellName];
                $value = $tableXpath->evaluate($cellExpr, $rowEl);
                $this->validateXpathResult($cellExpr, $value);
                $cellEl->nodeValue = $value;
            }
        }

        $rows = array();
        foreach ($tableXpath->query('//row') as $rowEl) {
            $row = array();
            foreach ($tableXpath->query('./cell', $rowEl) as $cellEl) {
                $row[$cellEl->getAttribute('name')] = $cellEl->nodeValue;
            }
            $rows[] = $row;
        }

        foreach ($rows as &$row) {
            foreach ($config['exclude'] as $exclude) {
                unset($row[$exclude]);
            }
        }

        return $rows;
    }

    private function configureFormatters(OutputFormatterInterface $formatter)
    {
        $formatter->setStyle(
            'title', new OutputFormatterStyle('white', null, array('bold'))
        );
        $formatter->setStyle(
            'description', new OutputFormatterStyle(null, null, array())
        );
    }

    private function validateXpathResult($cellExpr, $value)
    {
        if (!is_scalar($value)) {
            throw new \InvalidArgumentException(sprintf(
                'Expected XPath expression "%s" to evaluate to a scalar, got "%s"',
                $cellExpr, is_object($value) ? get_class($value) : gettype($value)
            ));
        }
    }
}
