<?php

namespace PhpBench\Expression;

use PhpBench\Expression\Ast\Node;

interface NodePrinter
{
    public function print(MainPrinter $printer, Node $node, array $params): ?string;
}
