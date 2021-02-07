<?php

namespace PhpBench\Assertion\Ast;

final class PercentageValue implements ExpressionNode
{
    /**
     * @var ExpressionNode
     */
    private $percentage;

    public function __construct(ExpressionNode $percentage)
    {
        $this->percentage = $percentage;
    }

    public function percentage(): ExpressionNode
    {
        return $this->percentage;
    }
}
