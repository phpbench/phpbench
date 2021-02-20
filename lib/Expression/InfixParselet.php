<?php

namespace PhpBench\Expression;

use PhpBench\Expression\Ast\Node;

interface InfixParselet extends Parselet
{
    public function parse(Parser $parser, Node $left, Tokens $tokens): Node;

    public function precedence(): int;
}
