<?php

namespace PhpBench\Assertion\Ast;

use PhpBench\Assertion\Ast\ExpressionNode;

class Comparison implements Assertion
{
    /**
     * @var Value
     */
    private $value1;
    /**
     * @var string
     */
    private $operator;
    /**
     * @var Value
     */
    private $value2;

    /**
     * @var ToleranceNode|null
     */
    private $tolerance;

    public function __construct(ExpressionNode $value1, string $operator, ExpressionNode $value2, ?ToleranceNode $tolerance = null)
    {
        $this->value1 = $value1;
        $this->operator = $operator;
        $this->value2 = $value2;
        $this->tolerance = $tolerance;
    }

    public function operator(): string
    {
        return $this->operator;
    }

    public function value2(): ExpressionNode
    {
        return $this->value2;
    }

    public function value1(): ExpressionNode
    {
        return $this->value1;
    }

    public function tolerance(): ?ToleranceNode
    {
        return $this->tolerance;
    }
}
