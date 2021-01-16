<?php

namespace PhpBench\Assertion;

use PhpBench\Assertion\Ast\Node;

interface ExpressionPrinter
{
    public function format(Node $node): string;
}
