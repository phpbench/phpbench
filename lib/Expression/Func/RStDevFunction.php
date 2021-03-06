<?php

namespace PhpBench\Expression\Func;

use PhpBench\Expression\Ast\BooleanNode;
use PhpBench\Expression\Ast\ListNode;
use PhpBench\Math\Statistics;

final class RStDevFunction
{
    public function __invoke(ListNode $values, ?BooleanNode $sample = null): float
    {
        return Statistics::rstdev($values->phpValues(), $sample ? $sample->value() : false);
    }
}
