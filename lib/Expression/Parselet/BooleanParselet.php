<?php

namespace PhpBench\Expression\Parselet;

use PhpBench\Expression\Ast\BooleanNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Parser;
use PhpBench\Expression\PrefixParselet;
use PhpBench\Expression\Token;
use PhpBench\Expression\Tokens;

class BooleanParselet implements PrefixParselet
{
    public function tokenType(): string
    {
        return Token::T_BOOLEAN;
    }

    public function parse(Parser $parser, Tokens $tokens): Node
    {
        $token = $tokens->chomp();

        if ($token->value === 'true') {
            return new BooleanNode(true);
        }

        return new BooleanNode(false);
    }
}
