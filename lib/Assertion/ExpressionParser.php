<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace PhpBench\Assertion;

use PhpBench\Assertion\Ast\Comparison;
use PhpBench\Assertion\Ast\FloatNode;
use PhpBench\Assertion\Ast\FunctionNode;
use PhpBench\Assertion\Ast\IntegerNode;
use PhpBench\Assertion\Ast\MemoryValue;
use PhpBench\Assertion\Ast\Node;
use PhpBench\Assertion\Ast\NumberNode;
use PhpBench\Assertion\Ast\PercentageValue;
use PhpBench\Assertion\Ast\PropertyAccess;
use PhpBench\Assertion\Ast\ThroughputValue;
use PhpBench\Assertion\Ast\TimeValue;
use PhpBench\Assertion\Ast\ToleranceNode;
use PhpBench\Assertion\Ast\ExpressionNode;
use PhpBench\Assertion\Exception\SyntaxError;
use PhpBench\Util\MemoryUnit;
use PhpBench\Util\TimeUnit;

final class ExpressionParser
{
    /**
     * @var Nodes
     */
    private $buffer;

    /**
     * @var Tokens
     */
    private $tokens;

    public function __construct()
    {
        $this->buffer = new Nodes();
    }

    public function parse(Tokens $tokens): Node
    {
        $this->tokens = $tokens;
        return $this->buildAst();
    }

    private function buildAst(): Node
    {
        return $this->parseExpression();
    }

    private function parseExpression(): ?ExpressionNode
    {
        while ($node = $this->parseNode()) {
            $this->buffer->push($node);
        }

        return $this->buffer->pop();
    }

    private function parseNode(): ?ExpressionNode
    {
        $token = $this->tokens->current;
        if (!$token) {
            return null;
        }

        switch ($token->type) {
            case Token::T_INTEGER:
                $token = $this->tokens->chomp(Token::T_INTEGER);
                return new IntegerNode((int)$token->value);
            case Token::T_FLOAT:
                $token = $this->tokens->chomp(Token::T_FLOAT);
                return new FloatNode((float)$token->value);
            case Token::T_NAME:
                return $this->parseName();
            case Token::T_COMPARATOR:
                return $this->parseComparator();
        }

        $this->tokens->chomp();
        throw $this->syntaxError('Do not know how to parse node');
    }

    private function parseName(): ExpressionNode
    {
        $names = [$this->tokens->chomp(Token::T_NAME)->value];
        while ($this->tokens->if(Token::T_DOT)) {
            $this->tokens->chomp(Token::T_DOT);
            $names[] = $this->tokens->chomp(Token::T_NAME)->value;
        }

        return new PropertyAccess($names);
    }

    private function parseComparator(): Comparison
    {
        $comparator = $this->tokens->chomp(Token::T_COMPARATOR);
        $left = $this->buffer->pop();
        $right = $this->parseExpression();

        return new Comparison($left, $comparator->value, $right);
    }

    private function syntaxError(string $message): SyntaxError
    {
        $out = [''];

        $token = $this->tokens->previous();

        if (!$token) {
            throw new SyntaxError(sprintf(
                'Could not parse expression "%s": %s',
                $this->tokens->toString(),
                $message
            ));
        }

        throw SyntaxError::forToken($this->tokens, $token, $message);
    }
}
