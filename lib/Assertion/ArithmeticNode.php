<?php

namespace PhpBench\Assertion;

use PhpBench\Assertion\Ast\ExpressionNode;
use PhpBench\Assertion\Ast\OperatorExpression;

class ArithmeticNode implements OperatorExpression
{
    /**
     * @var ExpressionNode
     */
    private $left;
    /**
     * @var string
     */
    private $operator;
    /**
     * @var ExpressionNode
     */
    private $right;

    public function __construct(ExpressionNode $left, string $operator, ExpressionNode $right)
    {
        $this->left = $left;
        $this->operator = $operator;
        $this->right = $right;
    }

    public function left(): ExpressionNode
    {
        return $this->left;
    }

    public function operator(): string
    {
        return $this->operator;
    }

    public function right(): ExpressionNode
    {
        return $this->right;
    }
}
