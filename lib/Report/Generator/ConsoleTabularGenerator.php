<?php

namespace PhpBench\Report\Generator;

use PhpBench\Tabular\Tabular;
use PhpBench\ReportGeneratorInterface;
use PhpBench\Benchmark\SuiteDocument;
use Symfony\Component\Console\Helper\Table;
use PhpBench\Console\OutputAwareInterface;
use Symfony\Component\Console\Output\OutputInterface;

require_once(__DIR__ . '/tabular/xpath_functions.php');

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

        if ($config['aggregate']) {
            $report = 'console_aggregate';
        } else {
            $report = 'console_iteration';
        }

        $definition = json_decode(file_get_contents(__DIR__ . '/tabular/' . $report . '.json'), true);
        $tableDom = $this->tabular->tabulate($document, $definition);

        if ($config['debug']) {
            $tableDom->formatOutput = true;
            $this->output->writeln('<info>Table XML</info>');
            $this->output->writeln($tableDom->saveXML());
        }

        if ($config['title']) {
            $this->output->writeln(sprintf('<title>%s</title>', $config['title']));
        }

        if ($config['description']) {
            $this->output->writeln(sprintf('<description>%s</description>', $config['description']));
        }

        $this->render($tableDom, $config);
    }

    private function render($tableDom, $config)
    {
        $rows = array();
        $row = null;
        foreach ($tableDom->xpath()->query('//row') as $rowEl) {
            $row = array();
            foreach ($tableDom->xpath()->query('.//cell', $rowEl) as $cellEl) {
                $colName = $cellEl->getAttribute('name');

                // exclude cells
                if (in_array($colName, $config['exclude'])) {
                    continue;
                }

                $row[$colName] = $cellEl->nodeValue;
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
