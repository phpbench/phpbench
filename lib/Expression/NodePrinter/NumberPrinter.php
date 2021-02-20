<?php

namespace PhpBench\Expression\NodePrinter;

use PhpBench\Expression\Ast\FloatNode;
use PhpBench\Expression\Ast\IntegerNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\NumberNode;
use PhpBench\Expression\Printer;
use PhpBench\Expression\NodePrinter;

class NumberPrinter implements NodePrinter
{
    public function print(Printer $printer, Node $node, array $params): ?string
    {
        if (!$node instanceof NumberNode) {
            return null;
        }

        return (string)$node->value();
    }
}
