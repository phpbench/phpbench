<?php

namespace PhpBench\Expression;

use PhpBench\Assertion\Ast\Node;
use PhpBench\Assertion\Token;
use PhpBench\Assertion\Tokens;

interface PrefixParselet extends Parselet
{
    public function parse(Parser $parser, Tokens $tokens): Node;
}
