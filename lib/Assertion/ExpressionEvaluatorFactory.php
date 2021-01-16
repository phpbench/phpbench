<?php

namespace PhpBench\Assertion;

use PhpBench\Assertion\Printer\NodePrinter;

final class ExpressionEvaluatorFactory
{
    /**
     * @param array<string,mixed> $args
     */
    public function createWithArgs(array $args): ExpressionEvaluator
    {
        return new ExpressionEvaluator(new NodePrinter($args), $args);
    }
}
