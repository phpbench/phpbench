<?php

namespace PhpBench\Expression\Ast;

class PercentDifferenceNode implements PhpValue
{
    /**
     * @var float
     */
    private $percentage;

    /**
     * @var float
     */
    private $tolerance;

    public function __construct(float $percentage, float $tolerance = 0)
    {
        $this->percentage = $percentage;
        $this->tolerance = $tolerance;
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

    public function tolerance(): float
    {
        return $this->tolerance;
    }
}
