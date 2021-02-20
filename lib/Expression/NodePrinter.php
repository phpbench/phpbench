<?php

namespace PhpBench\Expression;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\NormalizingPrinter;

interface NodePrinter
{
    public function print(Printer $printer, Node $node, array $params): ?string;
}
