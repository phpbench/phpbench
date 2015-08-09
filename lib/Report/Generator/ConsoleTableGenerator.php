<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Report\Generator;

use PhpBench\Result\Dumper\XmlDumper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;
use PhpBench\Benchmark\SuiteDocument;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use PhpBench\Report\Dom\PhpBenchXpath;
use PhpBench\Report\Tool\Sort;
use PhpBench\Report\Tool\Formatter;
use PhpBench\Console\OutputAwareInterface;
use PhpBench\ReportGeneratorInterface;

/**
 * Report which generates console based tabular reports
 * using XPath as a datasource.
 */
class ConsoleTableGenerator implements OutputAwareInterface, ReportGeneratorInterface
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var Formatter
     */
    private $formatter;

    /**
     * @var \DOMNode[]
     */
    private $postProcessElements = array();

    /**
     * @param XmlDumper $xmlDumper
     * @param Formatter $formatter
     */
    public function __construct(Formatter $formatter = null)
    {
        $this->formatter = $formatter ?: new Formatter();
    }

    /**
     * {@inheritDoc}
     */
    public function getSchema()
    {
        return array(
            'type' => 'object',
            'properties' => array(
                'debug' => array(
                    'description' => 'Enable to output debug information',
                    'type' => 'boolean',
                ),
                'title' => array(
                    'description' => 'Title of the report to display',
                    'oneOf' => array(
                        array('type' => 'string'),
                        array('type' => 'null'),
                    ),
                ),
                'description' => array(
                    'description' => 'Description of the report to display',
                    'oneOf' => array(
                        array('type' => 'string'),
                        array('type' => 'null'),
                    ),
                ),
                'rows' => array(
                    'type' => 'array',
                    'items' => array(
                        'type' => 'object',
                        'properties' => array(
                            'cells' => array(
                                'type' => 'object',
                            ),
                            'with_items' => array(
                                'type' => 'array',
                            ),
                            'with_query' => array(
                                'type' => 'string',
                            ),
                        ),
                        'additionalProperties' => false,
                    ),
                ),
                'format' => array(
                    'type' => 'object',
                ),
                'sort' => array(
                    'type' => 'array',
                ),
                'exclude' => array(
                    'type' => 'array',
                ),
                'params' => array(
                    'oneOf' => array(
                        array('type' => 'object'),
                        array('type' => 'array'),
                    ),
                ),
                'generator' => array(
                    'type' => 'string',
                ),
            ),
            'additionalProperties' => false,
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultConfig()
    {
        return array(
            'debug' => false,
            'title' => null,
            'description' => null,
            'rows' => array(),
            'format' => array(
                'revs' => '!number',
                'rps' => array('!number', '%s<comment>rps</comment>'),
                'time' => array('!number', '%s<comment>μs</comment>'),
                'deviation' => array('%.2f', '!balance', '%s<comment>%%</comment>'),
                'memory' => array('!number', '%s<comment>b</comment>'),
                'memory_diff' => array('!number', '!balance', '%s<comment>b</comment>'),
            ),
            'sort' => array(),
            'exclude' => array(),
            'params' => array(),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
        $this->configureFormatters($output->getFormatter());
    }

    /**
     * {@inheritDoc}
     */
    public function generate(SuiteDocument $suite, array $config)
    {
        if (null !== $config['title']) {
            $this->output->writeln(sprintf('<title>%s</title>', $config['title']));
        }

        if (null !== $config['description']) {
            $this->output->writeln(sprintf('<description>%s</description>', $config['description']));
        }

        if ($config['debug']) {
            $this->output->writeln('<info>Suite XML</info>');
            $this->output->writeln($suite->saveXML());
        }

        $tableDom = new \DOMDocument(1.0);

        $this->transformToTableDom($suite, $tableDom, $config);

        if ($config['debug']) {
            $tableDom->formatOutput = true;
            $this->output->writeln('<info>Table XML</info>');
            $this->output->writeln($tableDom->saveXML());
        }

        $rows = $this->postProcess($tableDom, $config);

        if (!empty($config['sort'])) {
            Sort::sortRows($rows, $config['sort']);
        }

        $row = null;
        foreach ($rows as &$row) {
            foreach ($row as $colName => &$value) {
                if (isset($config['format'][$colName])) {
                    $value = $this->formatter->format($value, $config['format'][$colName]);
                }
            }
        }

        $table = $this->createTable();
        $table->setHeaders(array_keys($row ?: array()));
        $table->setRows($rows);
        $this->renderTable($table);
        $this->output->writeln('');
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultReports()
    {
        return array(
            'default' => array(
                'debug' => false,
                'title' => null,
                'description' => null,
                'rows' => array(
                    array(
                        'cells' => array(
                            'benchmark' => 'string(php:bench(\'class_name\', string(ancestor-or-self::benchmark/@class)))',
                            'subject' => 'string(ancestor-or-self::subject/@name)',
                            'group' => 'string(ancestor-or-self::group/@name)',
                            'params' => 'php:bench(\'parameters_to_json\', ancestor::subject/parameter)',
                            'memory' => 'number(descendant-or-self::iteration/@memory)',
                            'revs' => 'number(descendant-or-self::iteration/@revs)',
                            'iter' => 'count(descendant-or-self::iteration/preceding-sibling::*)',
                            'time' => 'number(descendant-or-self::iteration/@time)',
                            'rps' => '(1000000 div number(descendant-or-self::iteration//@time)) * number(descendant-or-self::iteration/@revs)',
                            'deviation' => 'php:bench(\'deviation\', php:bench(\'min\', //@time), number(./@time))',
                        ),
                        'with_query' => '{{ param.selector }}',
                    ),
                ),
                'format' => array(
                    'revs' => '!number',
                    'rps' => array('!number', '%s<comment>rps</comment>'),
                    'time' => array('!number', '%s<comment>μs</comment>'),
                    'deviation' => array('%.2f', '!balance', '%s<comment>%%</comment>'),
                    'memory' => array('!number', '%s<comment>b</comment>'),
                    'memory_diff' => array('!number', '!balance', '%s<comment>b</comment>'),
                ),
                'sort' => array(),
                'exclude' => array(),
                'params' => array('selector' => '//iteration'),
            ),
            'aggregate' => array(
                'extends' => 'default',
                'rows' => array(
                    array(
                        'cells' => array(
                            'benchmark' => 'string(php:bench(\'class_name\', string(ancestor-or-self::benchmark/@class)))',
                            'subject' => 'string(ancestor-or-self::subject/@name)',
                            'revs' => 'number(sum(.//@revs))',
                            'iters' => 'number(count(descendant::iteration))',
                            'time' => 'number(php:bench(\'avg\', descendant::iteration/@time)) div number(sum(descendant::iteration/@revs))',
                            'rps' => '(1000000 div number(php:bench(\'avg\', descendant::iteration/@time)) * number(php:bench(\'avg\', (descendant::iteration/@revs))))',
                            'stability' => '100 - php:bench(\'deviation\', number(php:bench(\'min\', descendant::iteration/@time)), number(php:bench(\'avg\', descendant::iteration/@time)))',
                            'deviation' => array(
                                'expr' => 'number(php:bench(\'deviation\', number(php:bench(\'min\', //cell[@name="time"])), number(./cell[@name="time"])))',
                                'post_process' => true,
                            ),
                        ),
                    ),
                ),
                'params' => array('selector' => '//variant'),
                'exclude' => array('group', 'params', 'pid', 'memory', 'memory_diff', 'iter'),
                'format' => array(
                    'revs' => '!number',
                    'rps' => array('!number', '%s<comment>rps</comment>'),
                    'time' => array('%s'),
                    'stability' => array('%.2f', '%s<comment>%%</comment>'),
                    'deviation' => array('%.2f', '!balance', '%s<comment>%%</comment>'),
                ),
            ),
            'simple' => array(
                'extends' => 'default',
                'exclude' => array('benchmark', 'memory', 'memory_diff', 'params', 'pid', 'group'),
            ),
        );
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

        foreach ($config['rows'] as $rowConfig) {
            if (!isset($rowConfig['cells'])) {
                throw new \InvalidArgumentException(sprintf(
                    'The "rows" key must contain an array of row configurations,  and each configuration must contain at least a "cells" key with an array of key to expression pairs, got: %s',
                    print_r($rowConfig, true)
                ));
            }

            $contextQuery = '/';

            if (isset($rowConfig['with_query'])) {
                $contextQuery = $this->replaceParameters($rowConfig['with_query'], $config['params']);
            }

            $items = array(null);
            if (isset($rowConfig['with_items'])) {
                $items = $rowConfig['with_items'];
            }

            $contextEls = $xpath->query($contextQuery);

            if (false === $contextEls) {
                throw new \InvalidArgumentException(sprintf(
                    'Invalid context query "%s"', $contextQuery
                ));
            }

            foreach ($items as $item) {
                foreach ($contextEls as $contextEl) {
                    $tableRowEl = $tableDom->createElement('row');
                    $tableEl->appendChild($tableRowEl);

                    foreach ($rowConfig['cells'] as $colName => $cellExpr) {
                        $cellConfig = array();
                        $cellItems = array(null);

                        if (is_array($cellExpr)) {
                            $cellConfig = $cellExpr;

                            // todo: build this into the JSON schema
                            if (!array_key_exists('expr', $cellConfig)) {
                                throw new \InvalidArgumentException(
                                    'Cell configuration must have at least an "expr" key containing an XPath expression'
                                );
                            }
                            if (array_key_exists('with_items', $cellExpr)) {
                                $cellItems = $cellConfig['with_items'];
                            }

                            $cellExpr = $cellConfig['expr'];
                        }

                        foreach ($cellItems as $cellItem) {
                            $expr = $this->replaceItem($cellExpr, $item, 'row');
                            $expr = $this->replaceItem($expr, $cellItem, 'cell');
                            $name = $this->replaceItem($colName, $item, 'row');
                            $name = $this->replaceItem($name, $cellItem, 'cell');

                            $tableCellEl = $tableDom->createElement('cell');
                            $tableCellEl->setAttribute('name', $name);
                            $tableRowEl->appendChild($tableCellEl);

                            if (isset($cellConfig['post_process']) && true === $cellConfig['post_process']) {
                                $this->postProcessElements[] = $tableCellEl;
                                $tableCellEl->setAttribute('post-process', 1);
                                $value = $expr;
                            } else {
                                $value = $this->evaluateExpression($xpath, $expr, $contextEl, $config['params'] ?: array());
                            }

                            $tableCellEl->nodeValue = $value;
                        }
                    }
                }
            }
        }
    }

    /**
     * Evaluate XPath expressions defined in the "cells" configuration key that
     * are nominted to be applied to the "table" DOM instead of the "suite
     * result" DOM. This is effectively a compiler pass.
     *
     * NOTE: If further compiler passes are ever needed then the
     *       ['post_process'] configuration key could accept an array instead of any
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
        foreach ($tableXpath->query('//row/cell[@post-process="1"]') as $cellEl) {
            $cellExpr = $cellEl->nodeValue;
            $rowEls = $tableXpath->query('./ancestor::row', $cellEl);
            $rowEl = $rowEls->item(0);
            $value = $this->evaluateExpression($tableXpath, $cellExpr, $rowEl, $config['params'] ?: array());
            $cellEl->nodeValue = $value;
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

    /**
     * Adds some output formatters.
     *
     * @param OutputFormatterInterface
     */
    private function configureFormatters(OutputFormatterInterface $formatter)
    {
        $formatter->setStyle(
            'title', new OutputFormatterStyle('white', null, array('bold'))
        );
        $formatter->setStyle(
            'description', new OutputFormatterStyle(null, null, array())
        );
    }

    /**
     * Evaluate an XPath expression to a scalar value. If the value is FALSE then we assu,e
     * that the XPath expression is invalid. Evaluating to `false` is not supported because that
     * is what PHP returns if the expression is invalid.
     *
     * @param \DOMXpath $xpath
     * @param string $cellExpr
     * @param \DOMElement $contextEl
     *
     * @return scalar
     */
    private function evaluateExpression(\DOMXpath $xpath, $cellExpr, \DOMNode $contextEl, array $params)
    {
        $cellExpr = $this->replaceParameters($cellExpr, $params);
        $value = $xpath->evaluate($cellExpr, $contextEl);

        if (!is_scalar($value)) {
            throw new \InvalidArgumentException(sprintf(
                'Expected XPath expression "%s" to evaluate to a scalar, got "%s"',
                $cellExpr, is_object($value) ? get_class($value) : gettype($value)
            ));
        }

        if (false === $value) {
            throw new \InvalidArgumentException(sprintf(
                'XPath expression "%s" is invalid or it evaluated to false, in which case PHP doesn\'t allow us to know the difference' .
                ' between false and an invalid expression.',
                $cellExpr
            ));
        }

        return $value;
    }

    /**
     * Create the table class. For Symfony 2.4 support.
     *
     * @return object
     */
    private function createTable()
    {
        if (class_exists('Symfony\Component\Console\Helper\Table')) {
            return new Table($this->output);
        }

        return new \Symfony\Component\Console\Helper\TableHelper();
    }

    /**
     * Render the table. For Symfony 2.4 support.
     *
     * @param mixed $table
     */
    private function renderTable($table)
    {
        if (class_exists('Symfony\Component\Console\Helper\Table')) {
            $table->render();

            return;
        }
        $table->render($this->output);
    }

    /**
     * Replace an item. Note that currently "items" must be simple
     * string values.
     *
     * The "item" is the value for which should be substituted for the string "item"
     * "context" would either be "row" or "cell".
     *
     * An item looks like `{{ row.item }}`.
     *
     * NOTE: In the future arrays could be supported, whereby the array key with dot notation
     *       as: `{{ row.item.foobar }}`
     *
     * @param string $expression
     * @param string $item
     * @param string $context
     *
     * @return string
     */
    private function replaceItem($expression, $item, $context)
    {
        if (null === $item) {
            return $expression;
        }

        return preg_replace('/{{\s*?' . $context . '\.item\s*}}/', $item, $expression);
    }

    /**
     * Replace any parameters in a string (f.e. an XPath query.
     *
     * @param string $string
     * @param array $parameters
     *
     * @return string
     */
    private function replaceParameters($string, array $parameters)
    {
        foreach ($parameters as $key => $value) {
            $string = preg_replace('/{{\s*?param.' . $key . '\s*}}/', $value, $string);
        }

        return $string;
    }
}
