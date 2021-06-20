<?php

namespace PhpBench\Expression\Func;

use PhpBench\Expression\Ast\NumberNode;
use PhpBench\Expression\Ast\PercentDifferenceNode;
use PhpBench\Math\Statistics;

final class PercentDifferenceFunction
{
    public function __invoke(NumberNode $value1, NumberNode $value2): PercentDifferenceNode
    {
        return new PercentDifferenceNode(Statistics::percentageDifference(
            $value1->value(),
            $value2->value()
        ));
    }
}
