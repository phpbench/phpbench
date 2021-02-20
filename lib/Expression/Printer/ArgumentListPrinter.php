<?php

namespace PhpBench\Expression\Printer;

use PhpBench\Expression\Ast\ArgumentListNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\NormalizingPrinter;
use PhpBench\Expression\NodePrinter;

class ArgumentListPrinter implements NodePrinter
{
    public function print(NormalizingPrinter $printer, Node $node, array $params): ?string
    {
        if (!$node instanceof ArgumentListNode) {
            return null;
        }

        $out = [];
        foreach ($node->expressions() as $expression) {
            $out[] = $printer->print($expression, $params);
        }

        return implode(', ', $out);
    }
}
