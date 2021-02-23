<?php

namespace PhpBench\Expression\Parselet;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\ParenthesisNode;
use PhpBench\Expression\Parser;
use PhpBench\Expression\PrefixParselet;
use PhpBench\Expression\Token;
use PhpBench\Expression\Tokens;

class ParenthesisParselet implements PrefixParselet
{
    public function tokenType(): string
    {
        return Token::T_OPEN_PAREN;
    }

    public function parse(Parser $parser, Tokens $tokens): Node
    {
        $tokens->chomp();
        $expression = $parser->parseList($tokens);
        $tokens->chomp(Token::T_CLOSE_PAREN);

        return new ParenthesisNode($expression);
    }
}
