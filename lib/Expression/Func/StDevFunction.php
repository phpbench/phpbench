<?php

namespace PhpBench\Expression\Func;

use PhpBench\Expression\Ast\ListNode;
use PhpBench\Math\Statistics;

final class StDevFunction
{
    public function __invoke(ListNode $values, ?BooleanNode $sample = null): float
    {
        return Statistics::stdev($values->phpValues(), $sample ? $sample->value() : false);
    }
}
