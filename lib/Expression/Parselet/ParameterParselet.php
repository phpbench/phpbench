<?php

namespace PhpBench\Expression\Parselet;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\ParameterNode;
use PhpBench\Expression\Parser;
use PhpBench\Expression\PrefixParselet;
use PhpBench\Expression\Token;
use PhpBench\Expression\Tokens;

class ParameterParselet implements PrefixParselet
{
    public function tokenType(): string
    {
        return Token::T_NAME;
    }

    public function parse(Parser $parser, Tokens $tokens): Node
    {
        $segments = [$tokens->chomp()->value];

        while ($tokens->current()->type === Token::T_DOT) {
            $dot = $tokens->chomp();
            $segments[] = $tokens->chomp(Token::T_NAME)->value;
        }

        return new ParameterNode($segments);
    }
}
