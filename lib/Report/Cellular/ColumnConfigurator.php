<?php

namespace PhpBench\Report\Cellular;

use PhpBench\Report\Cellular\ColumnSpecification;

interface ColumnConfigurator
{
    /**
     * Add or remove columns to the column specification
     *
     * @param ColumnSpecification
     */
    public function configureColumns(ColumnSpecification $specification);
}
