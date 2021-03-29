<?php

namespace PhpBench\Expression;

use PhpBench\Expression\Ast\Node;

interface NodePrinter
{
    /**
     */
    public function print(Printer $printer, Node $node): ?string;
}
