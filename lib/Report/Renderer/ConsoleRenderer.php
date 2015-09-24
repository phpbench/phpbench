<?php

namespace PhpBench\Report\Renderer;

use PhpBench\Report\RendererInterface;
use PhpBench\Console\OutputAwareInterface;

class ConsoleRenderer implements RendererInterface, OutputAwareInterface
{
    /**
     * Render the table.
     *
     * @param mixed $tableDom
     * @param mixed $config
     */
    public function render(TableDom $tableDom, $config)
    {
        $rows = array();
        $row = null;
        foreach ($tableDom->xpath()->query('//row') as $rowEl) {
            $row = array();
            foreach ($tableDom->xpath()->query('.//cell', $rowEl) as $cellEl) {
                $colName = $cellEl->getAttribute('name');

                // exclude cells
                if (isset($config['exclude']) && in_array($colName, $config['exclude'])) {
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
}
