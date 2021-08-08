<?php

namespace PhpBench\Expression\Parselet;

use PhpBench\Expression\Ast\AccessNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\InfixParselet;
use PhpBench\Expression\Parser;
use PhpBench\Expression\Precedence;
use PhpBench\Expression\Token;
use PhpBench\Expression\Tokens;

class ArrayAccessParselet implements InfixParselet
{
    public function tokenType(): string
    {
        return Token::T_OPEN_LIST;
    }

    public function parse(Parser $parser, Node $left, Tokens $tokens): Node
    {
        $tokens->chomp(Token::T_OPEN_LIST);
        $access = $parser->parseExpression($tokens);
        $tokens->chomp(Token::T_CLOSE_LIST);

        return new AccessNode($left, $access);
    }

    public function precedence(): int
    {
        return Precedence::ACCESS;
    }
}
