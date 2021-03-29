<?php

namespace PhpBench\Expression\Func;

use PhpBench\Expression\Ast\BooleanNode;
use PhpBench\Expression\Ast\FloatNode;
use PhpBench\Expression\Ast\ListNode;
use PhpBench\Expression\Ast\RelativeDeviationNode;
use PhpBench\Expression\Ast\StringNode;
use PhpBench\Expression\Ast\UnitNode;
use PhpBench\Math\Statistics;

final class RStDevFunction
{
    public function __invoke(ListNode $values, ?BooleanNode $sample = null): RelativeDeviationNode
    {
        return new RelativeDeviationNode(
            new FloatNode(Statistics::rstdev($values->phpValues(), $sample ? $sample->value() : false)),
            new UnitNode(new StringNode('%'))
        );
    }
}
