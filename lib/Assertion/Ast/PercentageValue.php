<?php

namespace PhpBench\Assertion\Ast;

final class PercentageValue implements Value
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
}
