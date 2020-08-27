<?php

namespace PhpBench\Assertion\Ast;

use PhpBench\Math\FloatNumber;
use PhpBench\Math\Statistics;

final class PercentageValue extends Parameter
{
    /**
     * @var float
     */
    private $percentage;

    public function __construct(float $percentage)
    {
        $this->percentage = $percentage;
    }

    public function resolveValue(Arguments $arguments): float
    {
        return $this->percentage;
    }

    public function difference(float $leftValue, float $rightValue): bool
    {
        $diff = Statistics::percentageDifference($leftValue, $rightValue);
        return FloatNumber::isLessThanOrEqual($diff, $this->percentage);
    }
}
