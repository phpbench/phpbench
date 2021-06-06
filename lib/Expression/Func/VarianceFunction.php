<?php

namespace PhpBench\Expression\Func;

use PhpBench\Expression\Ast\BooleanNode;
use PhpBench\Expression\Ast\FloatNode;
use PhpBench\Expression\Ast\ListNode;
use PhpBench\Math\Statistics;

final class VarianceFunction
{
    public function __invoke(ListNode $values, ?BooleanNode $sample = null): FloatNode
    {
        return new FloatNode(Statistics::variance($values->nonNullPhpValues(), $sample ? $sample->value() : false));
    }
}
