<?php

namespace PhpBench\Expression\Func;

use PhpBench\Expression\Ast\BooleanNode;
use PhpBench\Expression\Ast\FloatNode;
use PhpBench\Expression\Ast\ListNode;
use PhpBench\Math\Statistics;

final class RStDevFunction
{
    public function __invoke(ListNode $values, ?BooleanNode $sample = null): FloatNode
    {
        return new FloatNode(Statistics::rstdev($values->phpValues(), $sample ? $sample->value() : false));
    }
}
