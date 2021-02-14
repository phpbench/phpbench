<?php

namespace PhpBench\Expression\Parselet;

use PhpBench\Assertion\Ast\IntegerNode;
use PhpBench\Assertion\Ast\Node;
use PhpBench\Assertion\Token;
use PhpBench\Assertion\Tokens;
use PhpBench\Expression\Parser;
use PhpBench\Expression\PrefixParselet;

class IntegerParselet implements PrefixParselet
{
    public function tokenType(): string
    {
        return Token::T_INTEGER;
    }

    public function parse(Parser $parser, Tokens $tokens): Node
    {
        return new IntegerNode((int)$tokens->chomp()->value);
    }
}
