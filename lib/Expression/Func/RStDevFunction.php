<?php

namespace PhpBench\Expression\Func;

use PhpBench\Expression\Ast\BooleanNode;
use PhpBench\Expression\Ast\FloatNode;
use PhpBench\Expression\Ast\ListNode;
use PhpBench\Expression\Ast\RelativeDeviationNode;
use PhpBench\Math\Statistics;

final class RStDevFunction
{
    public function __invoke(ListNode $values, ?BooleanNode $sample = null): RelativeDeviationNode
    {
        return new RelativeDeviationNode(
            new FloatNode(Statistics::rstdev(
                $values->nonNullPhpValues(),
                $sample ? $sample->value() : false
            ))
        );
    }
}
