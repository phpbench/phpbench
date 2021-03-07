<?php

namespace PhpBench\Expression\Func;

use PhpBench\Expression\Ast\FloatNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\NumberNode;
use PhpBench\Math\Statistics;

final class PercentDifferenceFunction
{
    public function __invoke(NumberNode $value1, NumberNode $value2): Node
    {
        return new FloatNode(Statistics::percentageDifference($value1->value(), $value2->value()));
    }
}
