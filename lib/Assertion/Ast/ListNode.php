<?php

namespace PhpBench\Assertion\Ast;

class ListNode implements ExpressionNode
{
    /**
     * @var ExpressionNode[]
     */
    private $expressions;

    /**
     * @param ExpressionNode[] $expressions
     */
    public function __construct(array $expressions)
    {
        $this->expressions = $expressions;
    }

    /**
     * @return ExpressionNode[]
     */
    public function expressions(): array
    {
        return $this->expressions;
    }
}
