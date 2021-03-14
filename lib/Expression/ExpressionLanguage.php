<?php

namespace PhpBench\Expression;

use PhpBench\Expression\Ast\Node;

interface ExpressionLanguage
{
    public function parse(string $expression): Node;
}
