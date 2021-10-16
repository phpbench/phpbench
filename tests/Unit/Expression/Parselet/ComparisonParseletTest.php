<?php

namespace PhpBench\Tests\Unit\Expression\Parselet;

use Generator;
use PhpBench\Expression\Ast\ComparisonNode;
use PhpBench\Expression\Ast\IntegerNode;
use PhpBench\Expression\Exception\EvaluationError;
use PhpBench\Tests\Unit\Expression\ParseletTestCase;

class ComparisonParseletTest extends ParseletTestCase
{
    /**
     * @return Generator<mixed>
     */
    public function provideParse(): Generator
    {
        yield [
            '1 < 2',
            new ComparisonNode(
                new IntegerNode(1),
                '<',
                new IntegerNode(2)
            )
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function provideEvaluate(): Generator
    {
        yield ['1 < 2', [], 'true'];

        yield ['2 < 1', [], 'false'];

        yield ['2 < 2', [], 'false'];

        yield ['1 <= 2', [], 'true'];

        yield ['2 <= 2', [], 'true'];

        yield ['3 <= 2', [], 'false'];

        yield ['2 = 2', [], 'true'];

        yield ['1 = 2', [], 'false'];

        yield ['2 > 1', [], 'true'];

        yield ['1 > 2', [], 'false'];

        yield ['2 > 2', [], 'false'];

        yield ['3 >= 2', [], 'true'];

        yield ['2 >= 2', [], 'true'];

        yield ['1 >= 2', [], 'false'];

        yield ['10 < 10 + 10 * 30 ms', [], 'true'];

        yield ['10 as ms <= 10 as ms', [], 'true'];

        yield ['"10" = "10"', [], 'true'];

        yield ['"10" = foobar["baz"]', [
            'foobar' => [
                'baz' => '10',
            ],
        ], 'true'];
    }

    public function testErrorOnUnsupportedOperator(): void
    {
        $this->expectException(EvaluationError::class);
        $this->evaluate($this->parse('"foo" < 2'));
    }

    /**
     * {@inheritDoc}
     */
    public function providePrint(): Generator
    {
        yield ['1 < 2', []];

        yield ['1 <= 2', []];

        yield ['2 = 2', []];

        yield ['2 > 1', []];

        yield ['2 > 2', []];

        yield ['3 >= 2', []];

        yield ['10 as ms <= 10 as ms', [], '0.010ms <= 0.010ms'];
    }
}
