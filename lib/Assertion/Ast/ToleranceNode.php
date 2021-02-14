<?php

namespace PhpBench\Assertion\Ast;

use PhpBench\Expression\Ast\Node;

class ToleranceNode implements Node
{
    /**
     * @var ExpressionNode
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
