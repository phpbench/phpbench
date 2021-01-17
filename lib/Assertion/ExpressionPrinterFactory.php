<?php

namespace PhpBench\Assertion;

interface ExpressionPrinterFactory
{
    /**
     * @param parameters $parameters
     */
    public function create(array $parameters): ExpressionPrinter;
}
