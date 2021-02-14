<?php

namespace PhpBench\Expression\Parselet;

use PhpBench\Assertion\Ast\FloatNode;
use PhpBench\Assertion\Ast\IntegerNode;
use PhpBench\Assertion\Ast\Node;
use PhpBench\Assertion\Token;
use PhpBench\Expression\PrefixParselet;

class FloatParselet implements PrefixParselet
{
    public function tokenType(): string
    {
        return Token::T_FLOAT;
    }

    public function parse(Token $token): Node
    {
        return new FloatNode((int)$token->value);
    }
}
