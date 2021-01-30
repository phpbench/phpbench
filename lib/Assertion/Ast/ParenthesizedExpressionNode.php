<?php

namespace PhpBench\Assertion\Ast;

class ParenthesizedExpressionNode implements Node
{
    /**
     * @var Node
     */
    private $value;

    public function __construct(Node $value)
    {
        $this->value = $value;
    }
}
