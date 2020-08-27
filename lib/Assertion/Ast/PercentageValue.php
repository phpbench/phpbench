<?php

namespace PhpBench\Assertion\Ast;

use PhpBench\Math\FloatNumber;
use PhpBench\Math\Statistics;

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
