<?php

namespace PhpBench\Expression\Parselet;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\UnitNode;
use PhpBench\Expression\SuffixParselet;
use PhpBench\Expression\Token;
use PhpBench\Expression\Tokens;

class UnitParselet implements SuffixParselet
{
    public function tokenType(): string
    {
        return Token::T_UNIT;
    }

    public function parse(Node $left, Tokens $tokens): Node
    {
        return new UnitNode($left, $tokens->chomp()->value);
    }
}
