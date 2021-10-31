<?php

namespace PhpBench\Expression\Parselet;

use PhpBench\Expression\Ast\AccessNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\StringNode;
use PhpBench\Expression\InfixParselet;
use PhpBench\Expression\Parser;
use PhpBench\Expression\Precedence;
use PhpBench\Expression\Token;
use PhpBench\Expression\Tokens;

class PropertyAccessParselet implements InfixParselet
{
    public function tokenType(): string
    {
        return Token::T_DOT;
    }

    public function parse(Parser $parser, Node $left, Tokens $tokens): Node
    {
        $tokens->chomp(Token::T_DOT);
        $name = $tokens->chomp(Token::T_NAME);

        return new AccessNode($left, new StringNode($name->value));
    }

    public function precedence(): int
    {
        return Precedence::ACCESS;
    }
}
