<?php

namespace PhpBench\Report\ComponentGenerator\TableAggregate;

use PhpBench\Data\DataFrame;
use PhpBench\Registry\RegistrableInterface;

interface ColumnProcessorInterface extends RegistrableInterface
{
    /**
     * @param parameters $params
     * @param tableRowArray $row
     * @param tableColumnDefinition $definition
     *
     * @return tableRowArray $row
     */
    public function process(array $row, array $definition, DataFrame $frame, array $params): array;
}
