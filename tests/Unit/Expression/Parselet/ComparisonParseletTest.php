<?php

namespace PhpBench\Tests\Unit\Expression\Parselet;

use Generator;
use PhpBench\Expression\Ast\ComparisonNode;
use PhpBench\Expression\Ast\IntegerNode;
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
        yield ['1 < 2', [], true];

        yield ['2 < 1', [], false];

        yield ['2 < 2', [], false];

        yield ['1 <= 2', [], true];

        yield ['2 <= 2', [], true];

        yield ['3 <= 2', [], false];

        yield ['2 = 2', [], true];

        yield ['1 = 2', [], false];

        yield ['2 > 1', [], true];

        yield ['1 > 2', [], false];

        yield ['2 > 2', [], false];

        yield ['3 >= 2', [], true];

        yield ['2 >= 2', [], true];

        yield ['1 >= 2', [], false];
    }
}
