<?php

namespace PhpBench\Expression\Parselet;

use PhpBench\Expression\Ast\ConcatNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\InfixParselet;
use PhpBench\Expression\Parser;
use PhpBench\Expression\Precedence;
use PhpBench\Expression\Token;
use PhpBench\Expression\Tokens;

class ConcatParselet implements InfixParselet
{
    public function tokenType(): string
    {
        return Token::T_TILDE;
    }

    public function parse(Parser $parser, Node $left, Tokens $tokens): Node
    {
        $binaryOperator = $tokens->chomp();
        $right = $parser->parseExpression($tokens);

        return new ConcatNode($left, $right);
    }

    public function precedence(): int
    {
        return Precedence::CONCAT;
    }
}
