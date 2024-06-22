<?php

namespace PhpBench\Expression\Ast;

class PercentDifferenceNode extends PhpValue
{
    public function __construct(private readonly float $percentage)
    {
    }

    public function percentage(): float
    {
        return $this->percentage;
    }

    /**
     * {@inheritDoc}
     */
    public function value()
    {
        return $this->percentage;
    }
}
