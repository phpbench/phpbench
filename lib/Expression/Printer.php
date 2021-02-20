<?php

namespace PhpBench\Expression;

use PhpBench\Expression\Ast\Node;

interface Printer
{
    /**
     * @param parameters $params
     */
    public function print(Node $node, array $params): string;
}
