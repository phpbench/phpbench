<?php

namespace PhpBench\Expression\Func;

use PhpBench\Expression\Ast\FloatNode;
use PhpBench\Expression\Ast\IntegerNode;
use PhpBench\Expression\Ast\ListNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Math\Statistics;

final class ModeFunction
{
    public function __invoke(ListNode $values, ?IntegerNode $space = null): Node
    {
        return new FloatNode(Statistics::kdeMode($values->phpValues(), $space ? $space->value() : 512));
    }
}
