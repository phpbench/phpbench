<?php

namespace PhpBench\Expression\NodePrinter;

use PhpBench\Expression\Ast\ArithmeticOperatorNode;
use PhpBench\Expression\Ast\BooleanNode;
use PhpBench\Expression\Ast\ComparisonNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Printer;
use PhpBench\Expression\NodePrinter;

class BooleanPrinter implements NodePrinter
{
    public function print(Printer $printer, Node $node, array $params): ?string
    {
        if (!$node instanceof BooleanNode) {
            return null;
        }

        return $node->value() ? 'true':'false';
    }
}
