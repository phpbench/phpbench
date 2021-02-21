<?php

namespace PhpBench\Expression\Parselet;

use PhpBench\Expression\Ast\DisplayAsNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\InfixParselet;
use PhpBench\Expression\Parser;
use PhpBench\Expression\Precedence;
use PhpBench\Expression\Token;
use PhpBench\Expression\Tokens;

class DisplayAsParselet implements InfixParselet
{
    public function tokenType(): string
    {
        return Token::T_AS;
    }

    public function parse(Parser $parser, Node $left, Tokens $tokens): Node
    {
        $tokens->chomp();
        $unit = $tokens->chomp(Token::T_UNIT)->value;

        return new DisplayAsNode($left, $unit);
    }

    public function precedence(): int
    {
        return Precedence::TOLERANCE;
    }
}
