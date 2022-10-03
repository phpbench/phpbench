<?php

namespace PhpBench\Expression\Ast;

class PercentDifferenceNode extends PhpValue
{
    /**
     * @var float
     */
    private $percentage;

    public function __construct(float $percentage)
    {
        $this->percentage = $percentage;
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
