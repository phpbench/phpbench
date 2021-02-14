<?php

namespace PhpBench\Expression\Parselet;

use PhpBench\Assertion\Ast\IntegerNode;
use PhpBench\Assertion\Ast\Node;
use PhpBench\Assertion\Token;
use PhpBench\Expression\PrefixParselet;

class IntegerParselet implements PrefixParselet
{
    public function tokenType(): string
    {
        return Token::T_INTEGER;
    }

    public function parse(Token $token): Node
    {
        return new IntegerNode((int)$token->value);
    }
}
