<?php

namespace PhpBench\Benchmark;

class AssertionFailure extends \Exception
{
    /**
     * @var array
     */
    private $context;

    /**
     * @var string
     */
    private $expression;

    public function __construct(string $expression, array $context)
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

    public function getExpression(): string
    {
        return $this->expression;
    }
}

