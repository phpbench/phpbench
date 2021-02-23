<?php

namespace PhpBench\Expression\Parselet;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\TolerableNode;
use PhpBench\Expression\InfixParselet;
use PhpBench\Expression\Parser;
use PhpBench\Expression\Precedence;
use PhpBench\Expression\Token;
use PhpBench\Expression\Tokens;

class TolerableParselet implements InfixParselet
{
    public function tokenType(): string
    {
        return Token::T_TOLERANCE;
    }

    public function parse(Parser $parser, Node $left, Tokens $tokens): Node
    {
        $tokens->chomp();

        return new TolerableNode($left, $parser->parseExpression($tokens, 100));
    }

    public function precedence(): int
    {
        return Precedence::TOLERANCE;
    }
}
