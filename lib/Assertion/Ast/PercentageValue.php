<?php

namespace PhpBench\Assertion\Ast;

use PhpBench\Assertion\Ast\ExpressionNode;

final class PercentageValue implements ExpressionNode
{
    /**
     * @var NumberNode
     */
    private $percentage;

    public function __construct(NumberNode $percentage)
    {
        $this->percentage = $percentage;
    }

    public function percentage(): NumberNode
    {
        return $this->percentage;
    }
}
