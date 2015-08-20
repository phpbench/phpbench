<?php

namespace PhpBench\Report\Generator;

use PhpBench\Tabular\Tabular;
use PhpBench\ReportGeneratorInterface;
use PhpBench\Benchmark\SuiteDocument;
use Symfony\Component\Console\Helper\Table;
use PhpBench\Console\OutputAwareInterface;
use Symfony\Component\Console\Output\OutputInterface;

require_once(__DIR__ . '/Tabular/xpath_functions.php');

class ConsoleTabularGenerator implements ReportGeneratorInterface, OutputAwareInterface
{
    private $tabular;
    private $output;

    public function __construct(Tabular $tabular)
    {
        $this->tabular = $tabular;
    }

    /**
     * {@inheritDoc}
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * {@inheritDoc}
     */
    public function getSchema()
    {
        return array(
            'type' => 'object',
            'additionalProperties' => false,
            'peoperties' => array(
                'title' => array(
                    'type' => 'string'
                ),
                'description' => array(
                    'type' => 'string'
                ),
                'aggregate' => array(
                    'type' => 'boolean'
                ),
                'exclude' => array(
                    'type' => 'array',
                ),
                'debug' => array(
                    'type' => 'boolean',
                ),
            ),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function generate(SuiteDocument $document, array $config)
    {
        if ($config['debug']) {
            $this->output->writeln('<info>Suite XML</info>');
            $this->output->writeln($document->saveXML());
        }

        $definition = array(
            'rows' => array(
                array(
                    'cells' => array(
                        'benchmark' => array(
                            'expr' => 'class_name(string(ancestor-or-self::benchmark/@class))',
                        ),
                        'subject' => array(
                            'expr' => 'string(ancestor-or-self::subject/@name)',
                        ),
                        'group' => array(
                            'expr' => 'string(ancestor-or-self::group/@name)',
                        ),
                        'params' => array(
                            'expr' => 'parameters_to_json(ancestor-or-self::variant/parameter)',
                        ),
                        'memory' => array(
                            'expr' => 'number(descendant-or-self::iteration/@memory)',
                        ),
                        'revs' => array(
                            'expr' => 'number(descendant-or-self::iteration/@revs)',
                        ),
                        'iter' => array(
                            'expr' => 'count(descendant-or-self::iteration/preceding-sibling::*)',
                        ),
                        'time' => array(
                            'class' => 'microsecond',
                            'expr' => 'number(descendant-or-self::iteration/@time) div number(sum(descendant-or-self::iteration/@revs))',
                        ),
                        'time_net' => array(
                            'class' => 'microsecond',
                            'expr' => 'number(descendant-or-self::iteration/@time)',
                        ),
                        'rps' => array(
                            'expr' => '(1000000 div number(descendant-or-self::iteration//@time)) * number(descendant-or-self::iteration/@revs)',
                        ),
                        'deviation' => array(
                            'class' => 'deviation',
                            'expr' => 'deviation(min(//@time), number(descendant-or-self::iteration/@time))',
                        ),
                    ),
                    'with_query' => '//iteration',
                ),
            ),
            'classes' => array(
                'microsecond' => array('printf', array('format' => '%s<comment>μs</comment>')),
                'deviation' => array('printf', array('format' => '%.2f')),
            ),
        );

        $tableDom = $this->tabular->tabulate($document, $definition);

        if ($config['debug']) {
            $tableDom->formatOutput = true;
            $this->output->writeln('<info>Table XML</info>');
            $this->output->writeln($tableDom->saveXML());
        }

        $rows = array();
        $row = null;
        foreach ($tableDom->xpath()->query('//row') as $rowEl) {
            $row = array();
            foreach ($tableDom->xpath()->query('.//cell', $rowEl) as $cellEl) {
                $row[$cellEl->getAttribute('name')] = $cellEl->nodeValue;
            }
            $rows[] = $row;
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
            'aggregate' => array(
                'aggregate' => true,
            ),
            'default' => array(
                'aggregate' => false,
            ),
        );
    }

    /***
     * {@inheritDoc}
     */
    public function getDefaultConfig()
    {
        return array(
            'debug' => false,
            'title' => null,
            'description' => null,
            'sort' => array(),
            'exclude' => array(),
            'aggregate' => false,
        );
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
}
