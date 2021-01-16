<?php

namespace PhpBench\Assertion;

final class ExpressionEvaluatorFactory
{
    /**
     * @var ExpressionFunctions
     */
    private $functions;

    public function __construct(ExpressionFunctions $functions)
    {
        $this->functions = $functions;
    }

    /**
     * @param array<string,mixed> $args
     */
    public function createWithArgs(array $args): ExpressionEvaluator
    {
        return new ExpressionEvaluator($args, $this->functions);
    }
}
