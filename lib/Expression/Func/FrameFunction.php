<?php

namespace PhpBench\Expression\Func;

use PhpBench\Data\DataFrame;
use PhpBench\Expression\Ast\DataFrameNode;
use PhpBench\Expression\Ast\FloatNode;
use PhpBench\Expression\Ast\IntegerNode;
use PhpBench\Expression\Ast\ListNode;
use PhpBench\Math\Statistics;

final class FrameFunction
{
    public function __invoke(ListNode $columns, ListNode $rows): DataFrameNode
    {
        return new DataFrameNode(DataFrame::fromRowSeries($rows->value(), $columns->value()));
    }
}
