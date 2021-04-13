<?php

namespace PhpBench\Expression\Parselet;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\PercentageNode;
use PhpBench\Expression\SuffixParselet;
use PhpBench\Expression\Token;
use PhpBench\Expression\Tokens;

class PercentageParselet implements SuffixParselet
{
    public function tokenType(): string
    {
        return Token::T_PERCENTAGE;
    }

    public function parse(Node $left, Tokens $tokens): Node
    {
        $tokens->chomp();

        return new PercentageNode($left);
    }
}
