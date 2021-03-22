<?php

namespace PhpBench\Expression\Parselet;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\NullNode;
use PhpBench\Expression\Parser;
use PhpBench\Expression\PrefixParselet;
use PhpBench\Expression\Token;
use PhpBench\Expression\Tokens;

class NullParselet implements PrefixParselet
{
    public function tokenType(): string
    {
        return Token::T_NULL;
    }

    public function parse(Parser $parser, Tokens $tokens): Node
    {
        $tokens->chomp();

        return new NullNode();
    }
}
