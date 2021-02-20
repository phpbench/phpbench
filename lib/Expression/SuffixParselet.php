<?php

namespace PhpBench\Expression;

use PhpBench\Expression\Ast\Node;

interface SuffixParselet extends Parselet
{
    public function parse(Node $left, Tokens $tokens): Node;
}
