<?php

namespace PhpBench\Expression\Printer;

use PhpBench\Expression\Ast\BinaryOperatorNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\MainPrinter;
use PhpBench\Expression\NodePrinter;

class BinaryOperatorPrinter implements NodePrinter
{
    public function print(MainPrinter $printer, Node $node, array $params): ?string
    {
        if (!$node instanceof BinaryOperatorNode) {
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
