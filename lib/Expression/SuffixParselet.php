<?php

namespace PhpBench\Expression;

use PhpBench\Expression\Ast\Node;

interface SuffixParselet extends Parselet
{
    public function parse(Parser $parser, Node $left, Tokens $tokens): Node;
}
