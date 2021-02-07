<?php

namespace PhpBench\Assertion\Ast;

class Comparison implements OperatorExpression
{
    /**
     * @var ExpressionNode
     */
    private $value1;
    /**
     * @var string
     */
    private $operator;
    /**
     * @var ExpressionNode
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
