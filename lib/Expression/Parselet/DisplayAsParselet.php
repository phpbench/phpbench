<?php

namespace PhpBench\Expression\Parselet;

use PhpBench\Expression\Ast\DisplayAsNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\StringNode;
use PhpBench\Expression\Exception\SyntaxError;
use PhpBench\Expression\InfixParselet;
use PhpBench\Expression\Parser;
use PhpBench\Expression\Precedence;
use PhpBench\Expression\Token;
use PhpBench\Expression\Tokens;

class DisplayAsParselet implements InfixParselet
{
    public function tokenType(): string
    {
        return Token::T_AS;
    }

    public function parse(Parser $parser, Node $left, Tokens $tokens): Node
    {
        $tokens->chomp();

        if ($tokens->current()->type === Token::T_UNIT) {
            $unit = $tokens->chomp()->value;
        } else {
            $unit = $parser->parseExpression($tokens);

            if (!$unit instanceof StringNode) {
                throw SyntaxError::forToken($tokens, $tokens->current(), 'Expected unit expression to evaluate to string');
            }

            $unit = $unit->value();
        }

        return new DisplayAsNode($left, $unit);
    }

    public function precedence(): int
    {
        return Precedence::TOLERANCE;
    }
}
