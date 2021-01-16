<?php

namespace PhpBench\Assertion\Ast;

final class PercentageValue implements Value
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
