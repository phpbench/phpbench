<?php

namespace PhpBench\Expression\Parselet;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\VariableNode;
use PhpBench\Expression\Parser;
use PhpBench\Expression\PrefixParselet;
use PhpBench\Expression\Token;
use PhpBench\Expression\Tokens;

class VariableParselet implements PrefixParselet
{
    public function tokenType(): string
    {
        return Token::T_NAME;
    }

    public function parse(Parser $parser, Tokens $tokens): Node
    {
        return new VariableNode($tokens->chomp()->value);
    }
}
