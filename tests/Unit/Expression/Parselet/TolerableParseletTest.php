<?php

namespace PhpBench\Tests\Unit\Expression\Parselet;

use Generator;
use PhpBench\Expression\Ast\ComparisonNode;
use PhpBench\Expression\Ast\IntegerNode;
use PhpBench\Expression\Ast\LogicalOperatorNode;
use PhpBench\Expression\Ast\PercentageNode;
use PhpBench\Expression\Ast\TolerableNode;
use PhpBench\Tests\Unit\Expression\ParseletTestCase;

class TolerableParseletTest extends ParseletTestCase
{
    /**
     * @return Generator<mixed>
     */
    public static function provideParse(): Generator
    {
        yield [
            '1 +/- 1',
            new TolerableNode(new IntegerNode(1), new IntegerNode(1))
        ];

        yield [
            '2 < 1 +/- 1',
            new ComparisonNode(
                new IntegerNode(2),
                '<',
                new TolerableNode(
                    new IntegerNode(1),
                    new IntegerNode(1)
                )
            )
        ];

        yield [
            '2 < 1 +/- 1 or 5 > 10 +/- 10%',
            new LogicalOperatorNode(
                new ComparisonNode(
                    new IntegerNode(2),
                    '<',
                    new TolerableNode(
                        new IntegerNode(1),
                        new IntegerNode(1)
                    )
                ),
                'or',
                new ComparisonNode(
                    new IntegerNode(5),
                    '>',
                    new TolerableNode(
                        new IntegerNode(10),
                        new PercentageNode(new IntegerNode(10))
                    )
                )
            )
        ];

        yield [
            '1 +/- 10%',
            new TolerableNode(
                new IntegerNode(1),
                new PercentageNode(new IntegerNode(10))
            )
        ];
    }

    /**
     * {@inheritDoc}
     */
    public static function provideEvaluate(): Generator
    {
        yield ['1 +/- 1', [], '1 ± 1'];

        yield ['2 < 2 +/- 1', [], '~true'];

        yield ['11 < 10 +/- 10%', [], '~true'];

        yield ['12 < 10 +/- 10%', [], 'false'];

        yield ['12 < 10 +/- 10%', [], 'false'];

        yield ['10 +/- 10%', [], '10 ± 1'];

        yield ['1000 +/- 10%', [], '1000 ± 100'];

        yield ['1000 as ms +/- 10%', [], '1.000ms ± 0.100ms'];
    }

    /**
     * {@inheritDoc}
     */
    public static function providePrint(): Generator
    {
        yield ['1 +/- 2', [], '1 ± 2'];
    }
}
