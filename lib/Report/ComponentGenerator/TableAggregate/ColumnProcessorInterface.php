<?php

namespace PhpBench\Report\ComponentGenerator\TableAggregate;

interface ColumnProcessorInterface
{
    /**
     * @param parameters $params
     * @param array<string,mixed> $row
     * @param array<string,mixed> $definition
     * @Return parameters
     */
    public function process(array $row, array $definition, array $params): array;
}
