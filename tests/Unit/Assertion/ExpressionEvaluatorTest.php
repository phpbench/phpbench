<?php

namespace PhpBench\Tests\Unit\Assertion;

use Generator;
use PhpBench\Assertion\Ast\Comparison;
use PhpBench\Assertion\Ast\IntegerNode;
use PhpBench\Assertion\Ast\Node;
use PhpBench\Assertion\ComparisonResult;
use PhpBench\Assertion\Exception\ExpressionEvaluatorError;
use PhpBench\Assertion\ExpressionEvaluator;
use PhpBench\Assertion\ExpressionFunctions;
use PhpBench\Assertion\ExpressionLexer;
use PhpBench\Assertion\ExpressionParser;
use PhpBench\Util\MemoryUnit;
use PhpBench\Util\TimeUnit;
use PHPUnit\Framework\TestCase;

class ExpressionEvaluatorTest extends TestCase
{
    /**
     * @dataProvider provideEvaluate
     *
     * @param array<string,mixed> $params
     * @param array<string,callable> $functions
     */
    public function testEvaluate(string $expression, array $params, $expected, array $functions = []): void
    {
        $functions = new ExpressionFunctions($functions);

        $eval = new ExpressionEvaluator($params, $functions);

        $lexer = new ExpressionLexer(
            $functions->names(),
            TimeUnit::supportedUnitNames(),
            MemoryUnit::supportedUnitNames()
        );
        $parser = new ExpressionParser($lexer);

        $result = $eval->evaluate(
            $parser->parse($lexer->lex($expression))
        );

        self::assertEquals(
            $expected,
            $result
        );
    }

    /**
     * @return Generator<mixed>
     */
    public function provideEvaluate(): Generator
    {
        // scalars
        yield 'int' => ['10', [], 10];

        yield 'float' => ['10.1', [], 10.1];

        // comparisons
        yield [
            '10 > 5',
            [],
            ComparisonResult::true()
        ];

        yield ['10 < 5', [], ComparisonResult::false()];

        yield ['5 < 5', [], ComparisonResult::false()];

        yield ['5 <= 5', [], ComparisonResult::true()];

        yield ['5 = 5', [], ComparisonResult::true()];

        yield ['5 = 4', [], ComparisonResult::false()];

        yield ['5 > 4', [], ComparisonResult::true()];

        yield ['4 > 4', [], ComparisonResult::false()];

        yield ['4 >= 4', [], ComparisonResult::true()];

        yield [
            '4 >= 4 +/- 1',
            [],
            ComparisonResult::tolerated()
        ];

        yield ['3 >= 4 +/- 1', [], ComparisonResult::tolerated()];

        yield ['2 >= 4 +/- 1', [], ComparisonResult::false()];

        yield ['5 >= 4 +/- 1', [], ComparisonResult::tolerated()];

        yield ['3 >= 4 +/- 30%', [], ComparisonResult::tolerated()];

        yield ['5 >= 4 +/- 30%', [], ComparisonResult::tolerated()];

        yield ['10 >= 4 +/- 30%', [], ComparisonResult::true()];

        yield ['0 >= 4 +/- 30%', [], ComparisonResult::false()];

        yield ['101 = 100 +/- 1%', [], ComparisonResult::tolerated()];

        // time units
        yield ['10', [], 10];

        yield ['10 microseconds', [], 10];

        yield ['1 ms', [], 1000.0];

        yield ['1 minutes', [], 6E7];

        // time unit comparison
        yield [
            '1 minute = 60 seconds',
            [],
            ComparisonResult::true()
        ];

        yield ['1 minute > 60 seconds', [], ComparisonResult::false()];

        yield ['1 minute > 60 seconds +/- 1 seconds', [], ComparisonResult::tolerated()];

        yield ['1 minute > 60 seconds +/- 0 seconds', [], ComparisonResult::false()];

        // throughput

        yield ['5 ops/second = 0.20 seconds', [], ComparisonResult::true()];

        yield ['4 ops/second = 0.20 seconds', [], ComparisonResult::false()];

        yield ['4 ops/second > 0.20 seconds', [], ComparisonResult::true()];

        yield ['4 ops/second > 0.20 seconds +/- 1 second', [], ComparisonResult::tolerated()];

        // memory
        yield ['10 kilobytes', [], 10000];

        yield ['10 megabytes', [], 10 * 1E6];

        yield ['10 bytes', [], 10];

        yield ['10 gb', [], 1E4 * 1E6];

        yield ['func(10) gb', [], 1E4 * 1E6, ['func' => function (int $val) {
            return $val;
        }]];

        // functions
        yield ['pass(12)', [], 12, ['pass' => function (int $val) {
            return $val;
        }]];

        // functions
        yield ['multiply(pass(12, 4), 2)', [], 24, [
            'pass' => function (int $val) {
                return $val;
            },
            'multiply' => function (int $val, int $multiplier) {
                return $val * $multiplier;
            }
        ]];

        yield ['multiply(pass(12), 2) > 10', [], ComparisonResult::true(), [
            'pass' => function (int $val) {
                return $val;
            },
            'multiply' => function (int $val, int $multiplier) {
                return $val * $multiplier;
            }
        ]];

        yield ['multiply(multiply(12, 2), 4)', [], 96, [
            'multiply' => function (int $val, int $multiplier) {
                return $val * $multiplier;
            }
        ]];

        // property access
        yield ['foo.bar', ['foo' => ['bar' => 10]], 10];

        yield ['foo.bar ms', ['foo' => ['bar' => 10]], 10000];

        yield ['foo.bar microseconds as ms', ['foo' => ['bar' => 10000]], 10E3];

        yield ['foo.bar ops/ms', [
            'foo' => ['bar' => 1000]
        ], 1.0];

        yield ['foo.bar as ms', ['foo' => ['bar' => 10000]], 10E3];

        yield ['multiply(multiply(12, foo.bar), 4)', [
            'foo' => ['bar' => 10]
        ], 480, [
            'multiply' => function (int $val, int $multiplier) {
                return $val * $multiplier;
            }
        ]];

        // arithmetic
        yield ['2+4', [], 6];

        yield ['2*2', [], 4];

        yield ['4/2', [], 2];

        yield ['4-2', [], 2];

        yield ['((4/2) * 2) + 6', [], 10];

        yield ['6 + (4 / 2) + (2 + 2)', [], 12];

        // lists
        yield ['[10,20]', [], [10,20]];
    }

    public function testErrorOnCannotEvaluate(): void
    {
        $this->expectException(ExpressionEvaluatorError::class);
        $node = new class() implements Node {
        };
        $eval = (new ExpressionEvaluator())->evaluate($node);
    }

    public function testErrorOnInvalidOperator(): void
    {
        $this->expectException(ExpressionEvaluatorError::class);
        $this->expectExceptionMessage('compare operator');
        $node = new Comparison(new IntegerNode(1), 'x', new IntegerNode(2));
        $eval = (new ExpressionEvaluator())->evaluate($node);
    }
}
