<?php

namespace PhpBench\Expression\Parselet;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\NullSafeNode;
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
        return new VariableNode($tokens->chomp()->value);

        $nullSafe = false;

        while (
            $tokens->current()->type === Token::T_DOT ||
            $tokens->current()->type === Token::T_QUESTION
        ) {
            $dot = $tokens->chomp();

            if ($dot->type === Token::T_QUESTION) {
                $nullSafe = true;

                continue;
            }

            $segment = $this->resolveSegment($parser, $tokens, $dot);

            if (null === $segment) {
                continue;
            }

            if ($nullSafe) {
                $segment = new NullSafeNode($segment);
            }

            $segments[] = $segment;
            $nullSafe = false;
        }

        return new ParameterNode($segments);
    }

    private function resolveSegment(Parser $parser, Tokens $tokens, Token $token): ?Node
    {
        if ($token->type === Token::T_DOT) {
            return new VariableNode($tokens->chomp(Token::T_NAME)->value);
        }

        if ($token->type === Token::T_OPEN_LIST) {
            $segment = $parser->parseExpression($tokens);
            $tokens->chomp(Token::T_CLOSE_LIST);

            return $segment;
        }

        return null;
    }
}
