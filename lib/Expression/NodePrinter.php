<?php

namespace PhpBench\Expression;

use PhpBench\Expression\Ast\Node;

interface NodePrinter
{
    /**
     * @param parameters $params
     */
    public function print(Printer $printer, Node $node, array $params): ?string;
}
