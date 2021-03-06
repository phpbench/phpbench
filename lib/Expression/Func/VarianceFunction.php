<?php

namespace PhpBench\Expression\Func;

use PhpBench\Expression\Ast\BooleanNode;
use PhpBench\Expression\Ast\ListNode;
use PhpBench\Math\Statistics;

final class VarianceFunction
{
    public function __invoke(ListNode $values, ?BooleanNode $sample = null): float
    {
        return Statistics::variance($values->phpValues(), $sample ? $sample->value() : false);
    }
}
