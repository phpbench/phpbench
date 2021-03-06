<?php

namespace PhpBench\Expression\Func;

use PhpBench\Expression\Ast\IntegerNode;
use PhpBench\Expression\Ast\ListNode;
use PhpBench\Math\Statistics;

final class ModeFunction
{
    public function __invoke(ListNode $values, ?IntegerNode $space = null): float
    {
        return Statistics::kdeMode($values->phpValues(), $space ? $space->value() : 512);
    }
}
