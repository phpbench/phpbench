<?php

namespace PhpBench\Expression;

use PhpBench\Assertion\Ast\Node;
use PhpBench\Assertion\Token;

interface InfixParselet extends Parselet
{
    public function parse(Parser $parser, Node $left, Token $token): Node;

    public function precedence(): int;
}
