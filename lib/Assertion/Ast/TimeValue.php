<?php

namespace PhpBench\Assertion\Ast;

use PhpBench\Util\TimeUnit;

class TimeValue implements ExpressionNode
{
    /**
     * @var ExpressionNode
     */
    private $value;

    /**
     * @var string
     */
    private $unit;

    public function __construct(ExpressionNode $value, ?string $unit = TimeUnit::MICROSECONDS)
    {
        $this->value = $value;
        $this->unit = TimeUnit::normalizeUnit($unit);
    }

    public function unit(): string
    {
        return $this->unit;
    }

    public function value(): ExpressionNode
    {
        return $this->value;
    }
}
