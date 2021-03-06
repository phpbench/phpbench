<?php

namespace PhpBench\Expression\Func;

use PhpBench\Expression\Ast\ListNode;
use PhpBench\Math\Statistics;

final class MeanFunction
{
    public function __invoke(ListNode $values): float
    {
        return Statistics::mean($values->phpValues());
    }
}
