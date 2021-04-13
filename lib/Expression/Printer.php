<?php

namespace PhpBench\Expression;

use PhpBench\Expression\Ast\Node;

interface Printer
{
    /**
     */
    public function print(Node $node): string;
}
