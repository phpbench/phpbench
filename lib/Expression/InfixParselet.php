<?php

namespace PhpBench\Expression;

use PhpBench\Assertion\Ast\Node;
use PhpBench\Assertion\Token;
use PhpBench\Assertion\Tokens;

interface InfixParselet extends Parselet
{
    public function parse(Parser $parser, Node $left, Tokens $tokens): Node;

    public function precedence(): int;
}
