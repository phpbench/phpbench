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
use PhpBench\Assertion\Ast\DisplayAsNode;
use PhpBench\Assertion\Ast\ExpressionNode;
use PhpBench\Assertion\Ast\FloatNode;
use PhpBench\Assertion\Ast\FunctionNode;
use PhpBench\Assertion\Ast\IntegerNode;
use PhpBench\Assertion\Ast\MemoryUnitNode;
use PhpBench\Assertion\Ast\MemoryValue;
use PhpBench\Assertion\Ast\Node;
use PhpBench\Assertion\Ast\PercentageValue;
use PhpBench\Assertion\Ast\PropertyAccess;
use PhpBench\Assertion\Ast\ThroughputValue;
use PhpBench\Assertion\Ast\TimeUnitNode;
use PhpBench\Assertion\Ast\TimeValue;
use PhpBench\Assertion\Ast\ToleranceNode;
use PhpBench\Assertion\Ast\UnitNode;
use PhpBench\Assertion\Exception\SyntaxError;

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
        $this->parseExpression();

        $result = $this->buffer->pop();

        if ($this->buffer->count()) {
            throw $this->syntaxError('Unexpected extra tokens before');
        }

        return $result;
    }

    private function parseExpression(): void
    {
        while ($node = $this->parseNode()) {
            $this->buffer->push($node);
        }
    }

    private function parseNode(): ?Node
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
            case Token::T_TOLERANCE:
                return $this->parseTolerance();
            case Token::T_TIME_UNIT:
                return $this->parseTimeUnit();
            case Token::T_MEMORY_UNIT:
                return $this->parseMemoryUnit();
            case Token::T_FUNCTION:
                return $this->parseFunction();
            case Token::T_AS:
                return $this->parseAs();
            case Token::T_PERCENTAGE:
                return $this->parsePercentage();
            case Token::T_THROUGHPUT:
                return $this->parseThroughput();

            // tokens which end expressions
            case Token::T_COMMA:
            case Token::T_CLOSE_PAREN:
                return null;
        }

        $this->tokens->chomp();

        throw $this->syntaxError('Do not know how to parse token');
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
        $left = $this->mustPopNode(ExpressionNode::class, 'Left hand side of comparison is missing');
        $this->parseExpression();
        $right = $this->mustShiftNode(ExpressionNode::class);
        $tolerance = $this->buffer->shiftType(ToleranceNode::class);

        return new Comparison(
            $left,
            $comparator->value,
            $right,
            $tolerance
        );
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

    private function parseTimeUnit(): TimeValue
    {
        $unit = $this->tokens->chomp(Token::T_TIME_UNIT);
        $expression = $this->mustPopNode(ExpressionNode::class, 'Expression expected before time unit');

        return new TimeValue($expression, $unit->value);
    }

    private function parsePercentage(): PercentageValue
    {
        $unit = $this->tokens->chomp(Token::T_PERCENTAGE);
        $expression = $this->mustPopNode(ExpressionNode::class, 'Expression expected before percentage');

        return new PercentageValue($expression);
    }

    private function parseMemoryUnit(): MemoryValue
    {
        $unit = $this->tokens->chomp(Token::T_MEMORY_UNIT);
        $expression = $this->mustPopNode(ExpressionNode::class, 'Expression expected before memory unit');

        return new MemoryValue($expression, $unit->value);
    }

    private function parseThroughput(): ThroughputValue
    {
        $this->tokens->chomp(Token::T_THROUGHPUT);
        $unit = $this->parseUnit();
        $expression = $this->mustPopNode(ExpressionNode::class);

        return new ThroughputValue($expression, $unit);
    }

    private function parseTolerance(): ToleranceNode
    {
        $this->tokens->chomp(Token::T_TOLERANCE);
        $this->parseExpression();
        $tolerance = $this->mustPopNode(ExpressionNode::class);

        return new ToleranceNode($tolerance);
    }

    private function parseFunction(): FunctionNode
    {
        $name = $this->tokens->chomp(Token::T_FUNCTION);
        $open = $this->tokens->chomp(Token::T_OPEN_PAREN);
        $values = $this->parseExpressionList();
        $close = $this->tokens->chomp(Token::T_CLOSE_PAREN);

        return new FunctionNode($name->value, $values);
    }

    /**
     * @return ExpressionNode[]
     */
    private function parseExpressionList(): array
    {
        $expressions = [];
        $this->parseExpression();

        while ($expression = $this->buffer->popType(ExpressionNode::class)) {
            $expressions[] = $expression;

            if ($this->tokens->if(Token::T_CLOSE_PAREN)) {
                return $expressions;
            }

            if ($this->tokens->if(Token::T_COMMA)) {
                $this->tokens->chomp();
            }

            $this->parseExpression();
        }

        return $expressions;
    }

    private function parseAs(): DisplayAsNode
    {
        $as = $this->tokens->chomp(Token::T_AS);
        $unit = $this->parseUnit();
        $expression = $this->mustPopNode(ExpressionNode::class);

        return new DisplayAsNode($expression, $unit);
    }

    private function parseUnit(): UnitNode
    {
        if ($this->tokens->if(Token::T_MEMORY_UNIT)) {
            return new MemoryUnitNode($this->tokens->chomp(Token::T_MEMORY_UNIT)->value);
        }

        return new TimeUnitNode($this->tokens->chomp(Token::T_TIME_UNIT)->value);
    }

    /**
     * @template T
     *
     * @param class-string<T> $nodeFqn
     *
     * @return T
     */
    private function mustPopNode(string $nodeFqn, ?string $message = null)
    {
        $node = $this->buffer->pop();

        if (null === $node) {
            throw $this->syntaxError(
                $message ?: 'Nothing left to pop'
            );
        }

        if (!$node instanceof $nodeFqn) {
            throw $this->syntaxError($message ?: sprintf(
                'Expected node of type "%s", got "%s"',
                $nodeFqn,
                get_class($node)
            ));
        }

        return $node;
    }

    /**
     * @template T
     *
     * @param class-string<T> $nodeFqn
     *
     * @return T
     */
    private function mustShiftNode(string $nodeFqn)
    {
        $node = $this->buffer->shift();

        if (!$node instanceof $nodeFqn) {
            throw $this->syntaxError(sprintf(
                'Expected node of type "%s", got "%s"',
                $nodeFqn,
                get_class($node)
            ));
        }

        return $node;
    }
}
