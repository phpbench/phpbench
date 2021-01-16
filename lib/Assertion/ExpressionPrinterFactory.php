<?php

namespace PhpBench\Assertion;

interface ExpressionPrinterFactory
{
    /**
     * @param array<string, mixed> $args
     */
    public function create(array $args): ExpressionPrinter;
}
