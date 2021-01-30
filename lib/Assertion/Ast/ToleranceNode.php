<?php

namespace PhpBench\Assertion\Ast;

class ToleranceNode implements Node
{
    /**
     * @var ExpresionNode
     */
    private $tolerance;

    public function __construct(ExpressionNode $tolerance)
    {
        $this->tolerance = $tolerance;
    }

    public function tolerance(): ExpressionNode
    {
        return $this->tolerance;
    }
}
