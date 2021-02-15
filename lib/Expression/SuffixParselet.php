<?php

namespace PhpBench\Expression;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Token;
use PhpBench\Expression\Tokens;

interface SuffixParselet extends Parselet
{
    public function parse(Node $left, Tokens $tokens): Node;
}
