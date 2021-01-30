<?php

namespace PhpBench\Assertion\Ast;

use PhpBench\Util\TimeUnit;
use PhpBench\Assertion\Ast\ExpressionNode;

class TimeValue implements ExpressionNode
{
    /**
     * @var Value
     */
    private $value;

    /**
     * @var string
     */
    private $unit;

    /**
     * @var string|null
     */
    private $asUnit;

    public function __construct(ExpressionNode $value, ?string $unit = TimeUnit::MICROSECONDS, ?string $asUnit = null)
    {
        $this->value = $value;
        $this->unit = TimeUnit::normalizeUnit($unit);
        $this->asUnit = $asUnit ? TimeUnit::normalizeUnit($asUnit) : null;
    }

    public function unit(): string
    {
        return $this->unit;
    }

    public function value(): ExpressionNode
    {
        return $this->value;
    }

    public function asUnit(): string
    {
        return $this->asUnit ?? $this->unit;
    }
}
