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
     * @param parameters $parameters
     */
    public function createWithParameters(array $parameters): ExpressionEvaluator
    {
        return new ExpressionEvaluator($parameters, $this->functions);
    }
}
