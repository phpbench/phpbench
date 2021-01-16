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
use RuntimeException;

class ExpressionParser
{
    /**
     * @var Node[]
     */
    private array $parts = [];

    /**
     * @var ExpressionLexer
     */
    private $lexer;

    public function __construct(ExpressionLexer $lexer)
    {
        $this->lexer = $lexer;
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

        $expression = $this->resolveNode();

        if (count($this->parts)) {
            throw new RuntimeException(sprintf(
                'Did not parse a single AST node, "%s" nodes remaining',
                count($this->parts)
            ));
        }

        return $expression;
    }

    private function resolveNode(bool $pop = false): ?Node
    {
        while ($this->lexer->lookahead) {
            $this->parts[] = $this->resolveToken();
        }

        if ($pop) {
            return array_pop($this->parts);
        }

        return array_shift($this->parts);
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
        $value = $this->lexer->lookahead['value'];
        $this->lexer->moveNext();
        $left = array_pop($this->parts);

        if (null === $left) {
            throw $this->syntaxError(sprintf(
                'Comparison "%s" has no left hand side', $value
            ));
        }

        if (!$left instanceof Value) {
            throw $this->syntaxError(sprintf(
                'Left hand side of "%s" must be a value got "%s"',
                $value, get_class($left)
            ));
        }

        $right = $this->resolveNode();

        if (null === $right) {
            throw $this->syntaxError(sprintf(
                'Comparison "%s" has no right hand side', $value
            ));
        }

        if (!$right instanceof Value) {
            throw $this->syntaxError(sprintf(
                'Right hand side of "%s" must be a value got "%s"',
                $value, get_class($right)
            ));
        }

        $tolerance = array_pop($this->parts);

        if (null !== $tolerance && !$tolerance instanceof ToleranceNode) {
            throw $this->syntaxError(sprintf(
                'Expected tolerance, got "%s"',
                get_class($tolerance)
            ));
        }

        return new Comparison($left, $value, $right, $tolerance);
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
        $unit = $this->lexer->lookahead['value'];
        $value = array_pop($this->parts);

        if (null === $value) {
            throw $this->syntaxError(sprintf(
                'Time unit "%s" has no value', $unit
            ));
        }

        if (!$value instanceof Value) {
            throw $this->syntaxError(sprintf('Expected "%s", got "%s"', Value::class, get_class($value)));
        }

        $this->lexer->moveNext();

        return new TimeValue($value, $unit);
    }

    private function parseMemoryUnit(): MemoryValue
    {
        $unit = $this->lexer->lookahead['value'];
        $value = array_pop($this->parts);

        if (null === $value) {
            throw $this->syntaxError(sprintf(
                'Time unit "%s" has no value', $unit
            ));
        }

        if (!$value instanceof NumberNode) {
            throw $this->syntaxError('Expected number');
        }

        $this->lexer->moveNext();

        return new MemoryValue($value, $unit);
    }

    private function parseTolerance(): ToleranceNode
    {
        $this->lexer->moveNext();
        $value = $this->resolveNode(true);

        if (!$value instanceof Value) {
            throw $this->syntaxError(sprintf(
                'Expected "%s", got "%s"', Value::class, get_class($value)
            ));
        }

        return new ToleranceNode($value);
    }

    private function parsePercentage(): PercentageValue
    {
        $value = $this->lexer->lookahead['value'];
        $node = array_pop($this->parts);

        if (!$node instanceof NumberNode) {
            throw $this->syntaxError(sprintf(
                'Expected number node for percentage, got "%s"',
                is_object($node) ? get_class($node) : gettype($node)
            ));
        }
        $this->lexer->moveNext();

        return new PercentageValue($node);
    }
}
