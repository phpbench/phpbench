<?php

namespace PhpBench\Expression\Func;

use PhpBench\Expression\Ast\FloatNode;
use PhpBench\Expression\Ast\IntegerNode;
use PhpBench\Expression\Ast\ListNode;
use PhpBench\Math\Statistics;

final class ModeFunction
{
    public function __invoke(ListNode $values, ?IntegerNode $space = null): FloatNode
    {
        return new FloatNode(Statistics::kdeMode($values->nonNullPhpValues(), $space ? $space->value() : 512));
    }
}
