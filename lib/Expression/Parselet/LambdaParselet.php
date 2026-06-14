<?php

namespace PhpBench\Expression\Parselet;

use PhpBench\Expression\Ast\ArgumentListNode;
use PhpBench\Expression\Ast\LambdaNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\ParenthesisNode;
use PhpBench\Expression\Ast\VariableNode;
use PhpBench\Expression\Exception\SyntaxError;
use PhpBench\Expression\InfixParselet;
use PhpBench\Expression\Parser;
use PhpBench\Expression\Precedence;
use PhpBench\Expression\Token;
use PhpBench\Expression\Tokens;

class LambdaParselet implements InfixParselet
{
    public function tokenType(): string
    {
        return Token::T_LAMBDA;
    }

    public function parse(Parser $parser, Node $left, Tokens $tokens): Node
    {
        $argList = $left;
        $apply = $tokens->chomp();

        if (!$argList instanceof ParenthesisNode) {
            throw SyntaxError::forToken($tokens, $apply, sprintf(
                'Left operand to lambda must be a parenthesized list of arguments, got: %s',
                $left::class
            ));
        }

        $argList = $argList->expression();

        if (!$argList instanceof ArgumentListNode) {
            throw SyntaxError::forToken($tokens, $apply, sprintf(
                'Left operand to lambda must be a parenthesized list of arguments, got: %s',
                $left::class
            ));
        }

        $expression = $parser->parseExpression($tokens);

        return new LambdaNode(new ArgumentListNode(array_map(function (Node $node) use ($tokens, $apply) {
            if (!$node instanceof VariableNode) {
                throw SyntaxError::forToken($tokens, $apply, sprintf(
                    'Invalid parameter of type: %s',
                    $node::class
                ));
            }

            return $node;
        }, $argList->nodes())), $expression);
    }

    public function precedence(): int
    {
        return Precedence::LAMBDA;
    }
}
