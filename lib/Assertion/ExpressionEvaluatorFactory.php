<?php

namespace PhpBench\Assertion;

use PhpBench\Assertion\MessageFormatter\NodeMessageFormatter;

final class ExpressionEvaluatorFactory
{
    /**
     * @param array<string,mixed> $args
     */
    public function createWithArgs(array $args): ExpressionEvaluator
    {
        return new ExpressionEvaluator(new NodeMessageFormatter($args), $args);
    }
}
