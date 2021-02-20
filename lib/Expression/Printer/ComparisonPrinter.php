<?php

namespace PhpBench\Expression\Printer;

use PhpBench\Expression\Ast\BinaryOperatorNode;
use PhpBench\Expression\Ast\ComparisonNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Printer;
use PhpBench\Expression\NodePrinter;

class ComparisonPrinter implements NodePrinter
{
    public function print(Printer $printer, Node $node, array $params): ?string
    {
        if (!$node instanceof ComparisonNode) {
            return null;
        }

        return sprintf(
            '%s %s %s',
            $printer->print($node->left(), $params),
            $node->operator(),
            $printer->print($node->right(), $params)
        );
    }
}
