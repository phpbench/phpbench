<?php

namespace PhpBench\Report\Cellular;

/**
 * Represents the available columns for a report.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class ColumnSpecification
{
    private $columns;

    public function setColumns(array $columns)
    {
        $this->columns = $columns;
    }

    public function getColumns()
    {
        return $this->columns;
    }
}
