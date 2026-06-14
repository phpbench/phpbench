<?php

namespace PhpBench\Expression\Parselet;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\SuffixParselet;
use PhpBench\Expression\Token;
use PhpBench\Expression\Tokens;

class CallableParselet implements SuffixParselet
{
    public function tokenType(): string
    {
        return Token::T_OPEN_PAREN;
    }

    public function parse(Node $left, Tokens $tokens): Node
    {
        throw new \RuntimeException('Need a parser!');
    }
}
