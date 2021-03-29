<?php

namespace PhpBench\Expression\NodePrinter;

use PhpBench\Expression\Ast\BinaryOperatorNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\NodePrinter;
use PhpBench\Expression\Printer;

class BinaryOperatorPrinter implements NodePrinter
{
    public function print(Printer $printer, Node $node): ?string
    {
        if (!$node instanceof BinaryOperatorNode) {
            return null;
        }

        return sprintf(
            '%s %s %s',
            $printer->print($node->left()),
            $node->operator(),
            $printer->print($node->right())
        );
    }
}
