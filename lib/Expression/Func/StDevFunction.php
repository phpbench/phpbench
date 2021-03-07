<?php

namespace PhpBench\Expression\Func;

use PhpBench\Expression\Ast\BooleanNode;
use PhpBench\Expression\Ast\FloatNode;
use PhpBench\Expression\Ast\ListNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Math\Statistics;

final class StDevFunction
{
    public function __invoke(ListNode $values, ?BooleanNode $sample = null): Node
    {
        return new FloatNode(Statistics::stdev($values->phpValues(), $sample ? $sample->value() : false));
    }
}
