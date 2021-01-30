<?php

namespace PhpBench\Assertion\Ast;

use PhpBench\Assertion\Ast\ExpressionNode;

class ToleranceNode implements Node
{
    /**
     * @var Value
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
