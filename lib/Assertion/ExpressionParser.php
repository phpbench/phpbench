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
use PhpBench\Assertion\Ast\TimeValue;
use PhpBench\Assertion\Ast\ToleranceNode;
use PhpBench\Assertion\Ast\Value;
use PhpBench\Assertion\Exception\SyntaxError;
use PhpBench\Util\MemoryUnit;
use PhpBench\Util\TimeUnit;
use RuntimeException;

class ExpressionParser
{
    /**
     * @var Nodes
     */
    private $nodes;

    /**
     * @var ExpressionLexer
     */
    private $lexer;

    public function __construct(ExpressionLexer $lexer)
    {
        $this->lexer = $lexer;
        $this->nodes = new Nodes();
    }

    public function parse(string $expression): Node
    {
        $this->lexer->setInput($expression);

        return $this->buildAst();
    }

    private function buildAst(): Node
    {
        $this->lexer->moveNext();
        $token = $this->lexer->token;

        $this->parseNextNode();

        return $this->nodes->singleRemainingNode();
    }

    private function parseNextNode(bool $pop = false): void
    {
        while ($this->lexer->lookahead) {
            $this->nodes->push($this->resolveToken());
        }
    }

    private function resolveToken(): ?Node
    {
        if (!$this->lexer->lookahead) {
            return null;
        }

        $type = $this->lexer->lookahead['type'];
        $value = $this->lexer->lookahead['value'];

        switch ($type) {
            case ExpressionLexer::T_INTEGER:
                $this->lexer->moveNext();

                return new IntegerNode($value);

            case ExpressionLexer::T_FLOAT:
                $this->lexer->moveNext();

                return new FloatNode($value);

            case ExpressionLexer::T_PROPERTY_ACCESS:
                return $this->parsePropertyAccess();
            case ExpressionLexer::T_COMPARATOR:
                return $this->parseComparison();
            case ExpressionLexer::T_FUNCTION:
                return $this->parseFunction();
            case ExpressionLexer::T_TIME_UNIT:
                return $this->parseTimeUnit();
            case ExpressionLexer::T_MEMORY_UNIT:
                return $this->parseMemoryUnit();
            case ExpressionLexer::T_TOLERANCE:
                return $this->parseTolerance();
            case ExpressionLexer::T_AS:
                return $this->parseAsUnit();
            case ExpressionLexer::T_PERCENTAGE:
                return $this->parsePercentage();
        }

        throw $this->syntaxError('Do not know how to parse token');
    }

    private function parsePropertyAccess(): PropertyAccess
    {
        $token = $this->lexer->lookahead;
        $this->lexer->moveNext();

        return new PropertyAccess(explode('.', $token['value']));
    }

    private function parseComparison(): Comparison
    {
        $comparator = $this->lexer->lookahead['value'];
        $this->lexer->moveNext();

        $left = $this->assureType(Value::class, $this->nodes->pop());
        $this->parseNextNode();
        $right = $this->assureType(Value::class, $this->nodes->shift());
        $tolerance = $this->assureTypeOrNull(ToleranceNode::class, $this->nodes->pop());

        return new Comparison($left, $comparator, $right, $tolerance);
    }

    private function syntaxError(string $message): SyntaxError
    {
        if (!$this->lexer->lookahead) {
            return new SyntaxError(sprintf(
                '%s. lookahead is empty', $message
            ));
        }

        return new SyntaxError(sprintf(
            '%s: (token "%s", position: %s, value: %s)',
            $message,
            $this->lexer->lookahead['type'],
            $this->lexer->lookahead['position'],
            json_encode($this->lexer->lookahead['value'])
        ));
    }

    private function parseFunction(): FunctionNode
    {
        $functionName = $this->lexer->lookahead['value'];

        $this->lexer->moveNext();

        $this->expect(ExpressionLexer::T_OPEN_PAREN);

        $args = [];

        while (true) {
            $arg = $this->resolveToken();

            if (!$arg instanceof Value) {
                throw $this->syntaxError('Expected value');
            }

            $args[] = $arg;

            $next = $this->lexer->lookahead;

            if (!$next) {
                throw $this->syntaxError('Unexpected end');
            }

            if ($next['type'] === ExpressionLexer::T_CLOSE_PAREN) {
                break;
            }

            $this->expect(ExpressionLexer::T_COMMA);
        }

        $this->expect(ExpressionLexer::T_CLOSE_PAREN);

        return new FunctionNode($functionName, $args);
    }

    private function expect(string $type): void
    {
        if (!$this->lexer->lookahead) {
            throw $this->syntaxError('No token to look ahead to');
        }

        if ($type === $this->lexer->lookahead['type']) {
            $this->lexer->moveNext();

            return;
        }

        throw $this->syntaxError(sprintf('Expected token "%s"', $type));
    }

    private function parseTimeUnit(): TimeValue
    {
        $value = $this->nodes->pop();
        $unit = $this->lexer->lookahead['value'];

        if (null === $value) {
            throw $this->syntaxError(sprintf(
                'Time unit "%s" has no value', $unit
            ));
        }

        if (!$value instanceof Value) {
            throw $this->syntaxError(sprintf('Expected "%s", got "%s"', Value::class, get_class($value)));
        }

        $this->lexer->moveNext();

        $asUnit = null;

        if ($this->lexer->lookahead && $this->lexer->lookahead['type'] === ExpressionLexer::T_AS) {
            $this->lexer->moveNext();
            $asUnit = $this->lexer->lookahead['value'];
            $this->lexer->moveNext();
        }

        return new TimeValue($value, $unit, $asUnit);
    }

    private function parseAsUnit(): Node
    {
        $value = $this->assureType(Value::class, $this->nodes->pop());

        $this->lexer->moveNext();
        $type = $this->lexer->lookahead['type'];
        $unit = $this->lexer->lookahead['value'];
        $this->lexer->moveNext();

        if ($type === ExpressionLexer::T_TIME_UNIT) {
            return new TimeValue($value, TimeUnit::MICROSECONDS, $unit);
        }

        if ($type === ExpressionLexer::T_MEMORY_UNIT) {
            return new MemoryValue($value, MemoryUnit::BYTES, $unit);
        }

        throw new RuntimeException(sprintf(
            'Expected memory or time unit token, got "%s"',
            $type
        ));
    }

    private function parseMemoryUnit(): MemoryValue
    {
        $unit = $this->lexer->lookahead['value'];
        $value = $this->assureType(Value::class, $this->nodes->pop());

        $this->lexer->moveNext();

        $asUnit = null;

        if ($this->lexer->lookahead && $this->lexer->lookahead['type'] === ExpressionLexer::T_AS) {
            $this->lexer->moveNext();
            $asUnit = $this->lexer->lookahead['value'];
            $this->lexer->moveNext();
        }

        return new MemoryValue($value, $unit, $asUnit);
    }

    private function parseTolerance(): ToleranceNode
    {
        $this->lexer->moveNext();
        $this->parseNextNode();
        $value = $this->assureType(Value::class, $this->nodes->pop());

        return new ToleranceNode($value);
    }

    private function parsePercentage(): PercentageValue
    {
        $value = $this->lexer->lookahead['value'];
        $node = $this->assureType(NumberNode::class, $this->nodes->pop());
        $this->lexer->moveNext();

        return new PercentageValue($node);
    }

    /**
     * @template T
     *
     * @param class-string<T> $classFqn
     * @param null|object $value
     *
     * @return T
     */
    private function assureType(string $classFqn, $value)
    {
        if (null === $value) {
            throw $this->syntaxError(sprintf(
                'Expected "%s", got NULL', Value::class
            ));
        }

        if (!($value instanceof $classFqn)) {
            throw $this->syntaxError(sprintf(
                'Expected "%s", got "%s"', Value::class, get_class($value)
            ));
        }

        return $value;
    }

    /**
     * @template T
     *
     * @param class-string<T> $classFqn
     * @param null|object $value
     *
     * @return T|null
     */
    private function assureTypeOrNull(string $classFqn, $value)
    {
        if (null === $value) {
            return $value;
        }

        if (!($value instanceof $classFqn)) {
            throw $this->syntaxError(sprintf(
                'Expected "%s", got "%s"', Value::class, get_class($value)
            ));
        }

        return $value;
    }
}
