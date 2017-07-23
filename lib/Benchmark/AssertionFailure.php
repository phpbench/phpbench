<?php

namespace PhpBench\Benchmark;

class AssertionFailure extends \Exception
{
    private $context;

    public function __construct($expression, array $context)
    {
        $this->context = $context;
        $this->expression = $expression;
        parent::__construct(sprintf(
            'Assertion "%s" failed, context: "%s"',
            $expression, json_encode($context)
        ));

    }

    public function getContext(): array
    {
        return $this->context;
    }
}
