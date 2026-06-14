<?php

namespace PhpBench\Expression\Parselet;

use PhpBench\Expression\Ast\ArgumentListNode;
use PhpBench\Expression\Ast\CallableNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\ParenthesisNode;
use PhpBench\Expression\Exception\SyntaxError;
use PhpBench\Expression\Parser;
use PhpBench\Expression\SuffixParselet;
use PhpBench\Expression\Token;
use PhpBench\Expression\Tokens;

class CallableParselet implements SuffixParselet
{
    public function tokenType(): string
    {
        return Token::T_OPEN_PAREN;
    }

    public function parse(Parser $parser, Node $left, Tokens $tokens): Node
    {
        $callable = $left;
        $tokens->chomp(Token::T_OPEN_PAREN);
        $args = [];
        if ($tokens->current()->type !== Token::T_CLOSE_PAREN) {
            $args = $parser->parseList($tokens);
            if (!$args instanceof ArgumentListNode) {
                $args = new ArgumentListNode([$args]);
            }
        }
        $tokens->chomp(Token::T_CLOSE_PAREN);

        if (!$callable instanceof ParenthesisNode) {
            throw SyntaxError::forToken(
                $tokens,
                $tokens->current(),
                'Callable expression must be parenthesized'
            );
        }

        return new CallableNode($callable->expression(), $args);
    }
}
