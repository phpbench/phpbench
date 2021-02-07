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
use PhpBench\Assertion\Ast\OperatorExpression;
use PhpBench\Assertion\Ast\ParenthesizedExpressionNode;
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

        $expression = $this->parseExpression();

        if ($this->tokens->current) {
            throw $this->syntaxError('Unexpected extra tokens');
        }

        return $expression;
    }

    private function parseExpression(): ExpressionNode
    {
        $expression = $this->parseNode();

        if ($this->tokens->if(Token::T_TIME_UNIT)) {
            $expression = $this->parseTimeUnit($expression);
        }

        if ($this->tokens->if(Token::T_MEMORY_UNIT)) {
            $expression = $this->parseMemoryUnit($expression);
        }

        if ($this->tokens->if(Token::T_THROUGHPUT)) {
            $expression = $this->parseThroughput($expression);
        }

        if ($this->tokens->if(Token::T_PERCENTAGE)) {
            $expression = $this->parsePercentage($expression);
        }

        if ($this->tokens->if(Token::T_AS)) {
            $expression = $this->parseAs($expression);
        }

        if ($this->tokens->if(Token::T_OPERATOR)) {
            return $this->parseOperator($expression);
        }

        return $expression;
    }

    private function parseNode(): ExpressionNode
    {
        $token = $this->tokens->current;

        switch ($token->type) {
            case Token::T_INTEGER:
                $token = $this->tokens->chomp(Token::T_INTEGER);
                return new IntegerNode((int)$token->value);
            case Token::T_FLOAT:
                $token = $this->tokens->chomp(Token::T_FLOAT);
                return new FloatNode((float)$token->value);
            case Token::T_NAME:
                return $this->parseName();
            case Token::T_FUNCTION:
                return $this->parseFunction();
            case Token::T_TIME_UNIT:
            case Token::T_MEMORY_UNIT:
                return $this->parseUnit();
            case Token::T_OPEN_PAREN:
                return $this->parseParenthesizedExpression();
        }

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

    private function parseArtithmetic(ExpressionNode $left, Token $operator, ExpressionNode $right): ArithmeticNode
    {
        return new ArithmeticNode(
            $left,
            $operator->value,
            $right
        );
    }

    private function syntaxError(string $message): SyntaxError
    {
        $out = [''];

        $token = $this->tokens->previous();

        if (!$token) {
            return new SyntaxError(sprintf(
                'Could not parse expression "%s": %s',
                $this->tokens->toString(),
                $message
            ));
        }

        return SyntaxError::forToken($this->tokens, $token, $message);
    }

    private function parseTimeUnit(ExpressionNode $value): TimeValue
    {
        $unit = $this->tokens->chomp(Token::T_TIME_UNIT);

        return new TimeValue($value, $unit->value);
    }

    private function parsePercentage(ExpressionNode $expression): PercentageValue
    {
        $unit = $this->tokens->chomp(Token::T_PERCENTAGE);
        return new PercentageValue($expression);
    }

    private function parseMemoryUnit(ExpressionNode $expression): MemoryValue
    {
        $unit = $this->tokens->chomp(Token::T_MEMORY_UNIT);

        return new MemoryValue($expression, $unit->value);
    }

    private function parseTolerance(): ToleranceNode
    {
        $this->tokens->chomp(Token::T_TOLERANCE);
        $value = $this->parseExpression();

        return new ToleranceNode($value);
    }

    private function parseFunction(): FunctionNode
    {
        $name = $this->tokens->chomp(Token::T_FUNCTION);
        $open = $this->tokens->chomp(Token::T_OPEN_PAREN);
        $values = $this->parseExpressionList();
        $close = $this->tokens->chomp(Token::T_CLOSE_PAREN);

        return new FunctionNode($name->value, $values);
    }

    private function parseParenthesizedExpression(): ParenthesizedExpressionNode
    {
        $this->tokens->chomp(Token::T_OPEN_PAREN);
        $expression = $this->parseExpression();
        $this->tokens->chomp(Token::T_CLOSE_PAREN);
        return new ParenthesizedExpressionNode($expression);
    }

    /**
     * @return ExpressionNode[]
     */
    private function parseExpressionList(): array
    {
        $expressions = [];

        while (true) {
            $expressions[] = $this->parseExpression();
            if ($this->tokens->if(Token::T_COMMA)) {
                $this->tokens->chomp();
                continue;
            }
            if ($this->tokens->if(Token::T_CLOSE_PAREN)) {
                return $expressions;
            }
            break;
        }

        throw $this->syntaxError('Invalid expression list');
    }

    private function parseAs(ExpressionNode $expression): DisplayAsNode
    {
        $as = $this->tokens->chomp(Token::T_AS);
        $unit = $this->parseUnit();

        return new DisplayAsNode($expression, $unit);
    }

    private function parseUnit(): UnitNode
    {
        if ($this->tokens->if(Token::T_MEMORY_UNIT)) {
            return new MemoryUnitNode($this->tokens->chomp(Token::T_MEMORY_UNIT)->value);
        }

        if ($this->tokens->if(Token::T_TIME_UNIT)) {
            return new TimeUnitNode($this->tokens->chomp(Token::T_TIME_UNIT)->value);
        }

        $this->tokens->chomp();

        throw $this->syntaxError('Unknown unit');
    }

    private function parseOperator(ExpressionNode $leftExpression): OperatorExpression
    {
        $operator = $this->tokens->chomp(Token::T_OPERATOR);

        switch ($operator->value) {
            case '<':
            case '<=':
            case '=':
            case '>':
            case '>=':
                $rightExpression = $this->parseExpression();
                return $this->parseComparison($leftExpression, $operator, $rightExpression);
            case '+':
            case '-':
            case '/':
            case '*':
                $rightExpression = $this->parseExpression();
                return $this->parseArtithmetic($leftExpression, $operator, $rightExpression);
        }

        throw $this->syntaxError('Unknown operator');
    }

    private function parseComparison(ExpressionNode $leftExpression, Token $operator, ExpressionNode $rightExpression): Comparison
    {
        $tolerance = null;

        if ($this->tokens->if(Token::T_TOLERANCE)) {
            $tolerance = $this->parseTolerance();
        }

        return new Comparison($leftExpression, $operator->value, $rightExpression, $tolerance);
    }

    private function parseThroughput(ExpressionNode $leftExpression): ThroughputValue
    {
        $this->tokens->chomp(Token::T_THROUGHPUT);
        return new ThroughputValue($leftExpression, $this->parseUnit());
    }
}
