<?php

namespace PhpBench\Expression\Printer;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\ParenthesisNode;
use PhpBench\Expression\MainPrinter;
use PhpBench\Expression\NodePrinter;

class ParenthesisPrinter implements NodePrinter
{
    public function print(MainPrinter $printer, Node $node, array $params): ?string
    {
        if (!$node instanceof ParenthesisNode) {
            return null;
        }

        return sprintf('(%s)', $printer->print($node->expression(), $params));
    }
}
