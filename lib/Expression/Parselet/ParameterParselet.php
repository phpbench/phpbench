<?php

namespace PhpBench\Expression\Parselet;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\ParameterNode;
use PhpBench\Expression\Ast\VariableNode;
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
        $segments = [new VariableNode($tokens->chomp()->value)];

        while (
            $tokens->current()->type === Token::T_DOT ||
            $tokens->current()->type === Token::T_OPEN_LIST
        ) {
            $dot = $tokens->chomp();

            if ($dot->type === Token::T_DOT) {
                $segments[] = new VariableNode($tokens->chomp(Token::T_NAME)->value);
            }

            if ($dot->type === Token::T_OPEN_LIST) {
                $segments[] = $parser->parseExpression($tokens);
                $tokens->chomp(Token::T_CLOSE_LIST);
            }
        }

        return new ParameterNode($segments);
    }
}
