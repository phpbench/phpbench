<?php

namespace PhpBench\Expression\Parselet;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\StringNode;
use PhpBench\Expression\Parser;
use PhpBench\Expression\PrefixParselet;
use PhpBench\Expression\Token;
use PhpBench\Expression\Tokens;

class StringParselet implements PrefixParselet
{
    public function tokenType(): string
    {
        return Token::T_STRING;
    }

    public function parse(Parser $parser, Tokens $tokens): Node
    {
        $string = (string)$tokens->chomp()->value;

        return new StringNode(trim($string, $string[0]));
    }
}
