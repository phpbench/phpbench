<?php

namespace PhpBench\Tests\Unit\Expression\Parselet;

use Generator;
use PhpBench\Expression\Ast\BinaryOperatorNode;
use PhpBench\Expression\Ast\ComparisonNode;
use PhpBench\Expression\Ast\IntegerNode;
use PhpBench\Expression\Ast\PercentageNode;
use PhpBench\Expression\Ast\TolerableNode;
use PhpBench\Expression\Value\TolerableValue;
use PhpBench\Expression\Value\ToleratedValue;
use PhpBench\Tests\Unit\Expression\ParseletTestCase;

class TolerableParseletTest extends ParseletTestCase
{
    /**
     * @return Generator<mixed>
     */
    public function provideParse(): Generator
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
            new BinaryOperatorNode(
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
                ),
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
    public function provideEvaluate(): Generator
    {
        yield ['1 +/- 1', [], new TolerableValue(1, 1)];

        yield ['2 < 2 +/- 1', [], new ToleratedValue(2)];

        yield ['11 < 10 +/- 10%', [], new ToleratedValue(11)];

        yield ['12 < 10 +/- 10%', [], false];

        yield ['12 < 10 +/- 10%', [], false];
    }
}
