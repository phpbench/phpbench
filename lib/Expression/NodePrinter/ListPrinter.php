<?php

namespace PhpBench\Expression\NodePrinter;

use PhpBench\Expression\Ast\ArgumentListNode;
use PhpBench\Expression\Ast\ListNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Printer;
use PhpBench\Expression\NodePrinter;

class ListPrinter implements NodePrinter
{
    public function print(Printer $printer, Node $node, array $params): ?string
    {
        if (!$node instanceof ListNode) {
            return null;
        }

        $out = [];
        foreach ($node->value() as $expression) {
            $out[] = $printer->print($expression, $params);
        }

        return '[' . implode(', ', $out) . ']';
    }
}
