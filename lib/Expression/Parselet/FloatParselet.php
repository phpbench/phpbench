<?php

namespace PhpBench\Expression\Parselet;

use PhpBench\Expression\Ast\FloatNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Parser;
use PhpBench\Expression\PrefixParselet;
use PhpBench\Expression\Token;
use PhpBench\Expression\Tokens;

class FloatParselet implements PrefixParselet
{
    public function tokenType(): string
    {
        return Token::T_FLOAT;
    }

    public function parse(Parser $parser, Tokens $tokens): Node
    {
        return new FloatNode((float)$tokens->chomp()->value);
    }
}
