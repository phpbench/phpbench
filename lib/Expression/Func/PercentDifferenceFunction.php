<?php

namespace PhpBench\Expression\Func;

use PhpBench\Expression\Ast\FloatNode;
use PhpBench\Expression\Ast\NumberNode;
use PhpBench\Math\Statistics;

final class PercentDifferenceFunction
{
    public function __invoke(NumberNode $value1, NumberNode $value2): float
    {
        return Statistics::percentageDifference($value1->value(), $value2->value());
    }
}
