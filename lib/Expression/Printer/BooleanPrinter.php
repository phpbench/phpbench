<?php

namespace PhpBench\Expression\Printer;

use PhpBench\Expression\Ast\BinaryOperatorNode;
use PhpBench\Expression\Ast\BooleanNode;
use PhpBench\Expression\Ast\ComparisonNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\MainPrinter;
use PhpBench\Expression\NodePrinter;

class BooleanPrinter implements NodePrinter
{
    public function print(MainPrinter $printer, Node $node, array $params): ?string
    {
        if (!$node instanceof BooleanNode) {
            return null;
        }

        return $node->value() ? 'true':'false';
    }
}
