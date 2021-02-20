<?php

namespace PhpBench\Expression;

use PhpBench\Expression\Ast\Node;

interface PrefixParselet extends Parselet
{
    public function parse(Parser $parser, Tokens $tokens): Node;
}
