<?php

namespace PhpBench\Expression\NodePrinter;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\NumberNode;
use PhpBench\Expression\NodePrinter;
use PhpBench\Expression\Printer;

class NumberPrinter implements NodePrinter
{
    public function __construct(private readonly int $precision = 12)
    {
    }

    public function print(Printer $printer, Node $node): ?string
    {
        if (!$node instanceof NumberNode) {
            return null;
        }

        return (string)round($node->value(), $this->precision);
    }
}
