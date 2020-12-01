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
use PhpBench\Assertion\Ast\MemoryValue;
use PhpBench\Assertion\Ast\Node;
use PhpBench\Assertion\Ast\PercentageValue;
use PhpBench\Assertion\Ast\PropertyAccess;
use PhpBench\Assertion\Ast\ThroughputValue;
use PhpBench\Assertion\Ast\TimeValue;
use PhpBench\Assertion\Ast\Value;
use PhpBench\Assertion\Ast\ZeroValue;
use PhpBench\Assertion\Exception\SyntaxError;
use PhpBench\Util\MemoryUnit;
use PhpBench\Util\TimeUnit;

class ExpressionParser
{
    /**
     * @var ExpressionLexer
     */
    private $lexer;

    public function parse(string $expression): Node
    {
        $this->lexer = new ExpressionLexer($expression);

        return $this->buildAst();
    }

    private function buildAst(): Node
    {
        $this->lexer->moveNext();

        return $this->parseComparisonExpression();
    }

    private function parseComparisonExpression(): Comparison
    {
        $left = $this->parseValue();
        $comparator = $this->parseComparator();
        $right = $this->parseValue();
        $tolerance = $this->parseTolerance();

        return new Comparison($left, $comparator, $right, $tolerance);
    }

    private function parseValue(): Value
    {
        switch ($this->lexer->lookahead['type']) {
            case ExpressionLexer::T_PROPERTY_ACCESS:
                return $this->parsePropertyAccess();
            case ExpressionLexer::T_INTEGER:
            case ExpressionLexer::T_FLOAT:
                $glimpsed = $this->lexer->glimpse();

                if ($glimpsed && $glimpsed['value'] === '%') {
                    return $this->parsePercentageValue();
                }

                return $this->parseUnitValue();
        }

        throw $this->syntaxError('property, integer or float', $this->lexer->lookahead);
    }

    private function parsePropertyAccess(): PropertyAccess
    {
        $token = $this->lexer->lookahead;
        $this->matchAndMoveNext($token, ExpressionLexer::T_PROPERTY_ACCESS);

        return new PropertyAccess(explode('.', $token['value']));
    }

    private function parseComparator(): string
    {
        $token = $this->lexer->lookahead;
        $this->matchAndMoveNext($token, ExpressionLexer::T_COMPARATOR);

        return $token['value'];
    }

    private function parseUnitValue(): Value
    {
        $value = $this->parseNumericValue();

        $token = $this->lexer->lookahead;
        $unit = $token ? $this->parseUnit($token) : TimeUnit::MICROSECONDS;

        if (TimeUnit::isTimeUnit($unit)) {
            return new TimeValue($value, $unit);
        }

        if (MemoryUnit::isMemoryUnit($unit)) {
            return new MemoryValue($value, $unit);
        }

        if (0 === strpos($unit, 'ops/')) {
            return new ThroughputValue($value, substr($unit, 4));
        }

        throw $this->syntaxError('time (e.g. microseconds), memory (e.g. bytes) or throughput unit (e.g. ops/second)', $token);
    }

    /**
     * @return int|float
     */
    private function parseNumericValue()
    {
        $token = $this->lexer->lookahead;

        if ($token['type'] === ExpressionLexer::T_INTEGER) {
            $this->lexer->moveNext();

            return (int)$token['value'];
        }

        if ($token['type'] === ExpressionLexer::T_FLOAT) {
            $this->lexer->moveNext();

            return (float)$token['value'];
        }

        throw $this->syntaxError('integer or float', $token);
    }

    private function parseUnit(?array $token): string
    {
        $this->matchAndMoveNext($token, ExpressionLexer::T_UNIT);

        return $token['value'];
    }

    private function parseTolerance(): Value
    {
        $token = $this->lexer->lookahead;

        if (null === $token) {
            return new ZeroValue();
        }

        $this->matchAndMoveNext($token, ExpressionLexer::T_TOLERANCE);

        return $this->parseValue();
    }

    private function parsePercentageValue(): PercentageValue
    {
        $token = $this->lexer->lookahead;
        $this->matchAndMoveNext($token, ExpressionLexer::T_INTEGER, ExpressionLexer::T_FLOAT);
        $value = $token['value'];
        $token = $this->lexer->lookahead;
        $this->matchAndMoveNext($token, ExpressionLexer::T_PERCENTAGE);

        return new PercentageValue((float)$value);
    }

    /**
     * @param array<mixed> $token
     */
    private function matchAndMoveNext(array $token, string ...$expectedTypes): void
    {
        if (in_array($token['type'], $expectedTypes)) {
            $this->lexer->moveNext();

            return;
        }

        throw $this->syntaxError(implode('", "', $expectedTypes), $token);
    }

    /**
     * @param array<mixed> $token
     */
    private function syntaxError(string $expected = '', ?array $token = null): SyntaxError
    {
        if ($token === null) {
            $token = $this->lexer->lookahead;
        }

        $tokenPos = $token['position'] ?? '-1';

        $message = sprintf('line 0, col %d: Error: ', $tokenPos);
        $message .= $expected !== '' ? sprintf('Expected %s, got ', $expected) : 'Unexpected ';
        $message .= $this->lexer->lookahead === null ? 'end of string.' : sprintf('"%s"', $token['value']);

        return new SyntaxError($message);
    }
}
