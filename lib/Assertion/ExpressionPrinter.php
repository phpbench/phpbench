<?php

namespace PhpBench\Assertion;

use PhpBench\Expression\Ast\Node;

interface ExpressionPrinter
{
    public function format(Node $node): string;
}
