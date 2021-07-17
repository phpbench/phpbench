<?php

namespace PhpBench\Expression\Parselet;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\NullSafeNode;
use PhpBench\Expression\InfixParselet;
use PhpBench\Expression\Parser;
use PhpBench\Expression\Precedence;
use PhpBench\Expression\Token;
use PhpBench\Expression\Tokens;

class NullSafeParselet implements InfixParselet
{
    public function tokenType(): string
    {
        return Token::T_QUESTION;
    }

    public function parse(Parser $parser, Node $left, Tokens $tokens): Node
    {
        $tokens->chomp();

        return new NullSafeNode($left);
    }

    public function precedence(): int
    {
        return Precedence::COMPARISON_EQUALITY;
    }
}
