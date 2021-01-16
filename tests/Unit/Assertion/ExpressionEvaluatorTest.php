<?php

namespace PhpBench\Tests\Unit\Assertion;

use Generator;
use PhpBench\Assertion\Ast\Comparison;
use PhpBench\Assertion\Ast\IntegerNode;
use PhpBench\Assertion\Ast\ThroughputValue;
use PhpBench\Assertion\Ast\TimeValue;
use PhpBench\Assertion\ComparisonResult;
use PhpBench\Assertion\ExpressionEvaluator;
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
     * @param mixed $expected
     * @param array<string,mixed> $params
     */
    public function testEvaluate(string $expression, array $params, $expected): void
    {
        $eval = new ExpressionEvaluator($params);

        $parser = new ExpressionParser(new ExpressionLexer(
            [],
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

    public function provideEvaluate(): Generator
    {
        yield 'int' => [
            '10',
            [],
            10
        ];

        yield 'float' => [
            '10.1',
            [],
            10.1
        ];

        yield ['10 > 5', [], ComparisonResult::true()];
        yield ['10 < 5', [], ComparisonResult::false()];
        yield ['5 < 5', [], ComparisonResult::false()];
        yield ['5 <= 5', [], ComparisonResult::true()];
        yield ['5 = 5', [], ComparisonResult::true()];
        yield ['5 = 4', [], ComparisonResult::false()];
        yield ['5 > 4', [], ComparisonResult::true()];
        yield ['4 > 4', [], ComparisonResult::false()];
        yield ['4 >= 4', [], ComparisonResult::true()];
        yield ['4 >= 4 +/- 10', [], ComparisonResult::tolerated()];

        yield 'memory' => [
            '10 megabytes',
            [],
            10 * 1E6
        ];

    }
}
