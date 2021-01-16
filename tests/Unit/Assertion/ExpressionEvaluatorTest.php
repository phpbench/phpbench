<?php

namespace PhpBench\Tests\Unit\Assertion;

use Generator;
use PhpBench\Assertion\Ast\Comparison;
use PhpBench\Assertion\Ast\IntegerNode;
use PhpBench\Assertion\Ast\ThroughputValue;
use PhpBench\Assertion\Ast\TimeValue;
use PhpBench\Assertion\ComparisonResult;
use PhpBench\Assertion\ExpressionEvaluator;
use PhpBench\Assertion\ExpressionFunctions;
use PhpBench\Assertion\ExpressionLexer;
use PhpBench\Assertion\ExpressionParser;
use PhpBench\Assertion\MessageFormatter\NodeMessageFormatter;
use PHPUnit\Framework\TestCase;
use PhpBench\Util\MemoryUnit;
use PhpBench\Util\TimeUnit;

class ExpressionEvaluatorTest extends TestCase
{
    /**
     * @dataProvider provideEvaluate
     *
     * @param mixed $expected
     * @param array<string,mixed> $params
     * @param array<string,callable> $functions
     */
    public function testEvaluate(string $expression, array $params, $expected, array $functions = []): void
    {
        $functions = new ExpressionFunctions($functions);

        $eval = new ExpressionEvaluator($params, $functions);

        $parser = new ExpressionParser(new ExpressionLexer(
            $functions->names(),
            TimeUnit::supportedUnitNames(),
            MemoryUnit::supportedUnitNames()
        ));

        $result = $eval->evaluate(
            $parser->parse($expression)
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
        yield ['10 > 5', [], ComparisonResult::true()];
        yield ['10 < 5', [], ComparisonResult::false()];
        yield ['5 < 5', [], ComparisonResult::false()];
        yield ['5 <= 5', [], ComparisonResult::true()];
        yield ['5 = 5', [], ComparisonResult::true()];
        yield ['5 = 4', [], ComparisonResult::false()];
        yield ['5 > 4', [], ComparisonResult::true()];
        yield ['4 > 4', [], ComparisonResult::false()];
        yield ['4 >= 4', [], ComparisonResult::true()];
        yield ['4 >= 4 +/- 1', [], ComparisonResult::tolerated()];
        yield ['3 >= 4 +/- 1', [], ComparisonResult::tolerated()];
        yield ['2 >= 4 +/- 1', [], ComparisonResult::false()];
        yield ['5 >= 4 +/- 1', [], ComparisonResult::tolerated()];
        yield ['3 >= 4 +/- 30%', [], ComparisonResult::tolerated()];
        yield ['5 >= 4 +/- 30%', [], ComparisonResult::tolerated()];
        yield ['10 >= 4 +/- 30%', [], ComparisonResult::true()];
        yield ['0 >= 4 +/- 30%', [], ComparisonResult::false()];

        // time units
        yield ['10', [], 10];
        yield ['10 microseconds', [], 10];
        yield ['1 ms', [], 1000.0];
        yield ['1 minutes', [], 6E7];

        // time unit comparison
        yield ['1 minute = 60 seconds', [], ComparisonResult::true()];
        yield ['1 minute > 60 seconds', [], ComparisonResult::false()];
        yield ['1 minute > 60 seconds +/- 1 seconds', [], ComparisonResult::tolerated()];
        yield ['1 minute > 60 seconds +/- 0 seconds', [], ComparisonResult::false()];

        // memory
        yield ['10 kilobytes', [], 10000];
        yield ['10 megabytes', [], 10 * 1E6];
        yield ['10 bytes', [], 10];
        yield ['10 gb', [], 1E4 * 1E6];

        // functions
        yield ['pass(12)', [], 12, ['pass' => function (int $val) {
            return $val;
        }]];

        // functions
        yield ['multiply(pass(12), 2)', [], 24, [
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
    }
}
