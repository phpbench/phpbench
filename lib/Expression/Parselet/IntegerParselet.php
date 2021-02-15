<?php

namespace PhpBench\Expression\Parselet;

use PhpBench\Expression\Ast\IntegerNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Parser;
use PhpBench\Expression\PrefixParselet;
use PhpBench\Expression\Token;
use PhpBench\Expression\Tokens;

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
