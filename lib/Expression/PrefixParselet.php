<?php

namespace PhpBench\Expression;

use PhpBench\Assertion\Ast\Node;
use PhpBench\Assertion\Token;

interface PrefixParselet extends Parselet
{
    public function parse(Token $token): Node;
}
