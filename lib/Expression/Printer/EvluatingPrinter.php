<?php

namespace PhpBench\Expression\Printer;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Printer;

class EvluatingPrinter implements Printer
{
    public function __construct(
        Printer $printer,

    /**
     * {@inheritDoc}
     */
    public function print(Node $node, array $params): string
    {
    }
}
