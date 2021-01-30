<?php

namespace PhpBench\Assertion\Ast;

class ParenthesizedExpressionNode implements ExpressionNode
{
    /**
     * @var ExpressionNode
     */
    private $expression;

    public function __construct(ExpressionNode $expression)
    {
        $this->expression = $expression;
    }

    public function expression(): ExpressionNode
    {
        return $this->expression;
    }
}
