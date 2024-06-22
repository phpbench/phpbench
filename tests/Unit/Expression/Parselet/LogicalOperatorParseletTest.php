<?php

namespace PhpBench\Tests\Unit\Expression\Parselet;

use Generator;
use PhpBench\Expression\Ast\IntegerNode;
use PhpBench\Expression\Ast\LogicalOperatorNode;
use PhpBench\Tests\Unit\Expression\ParseletTestCase;

class LogicalOperatorParseletTest extends ParseletTestCase
{
    /**
     * @return Generator<mixed>
     */
    public static function provideParse(): Generator
    {
        yield [
            '1 and 2',
            new LogicalOperatorNode(
                new IntegerNode(1),
                'and',
                new IntegerNode(2)
            )
        ];
    }

    /**
     * {@inheritDoc}
     */
    public static function provideEvaluate(): Generator
    {
        yield ['3 > 2 and 4 > 2', [], 'true'];

        yield ['3 > 2 and 4 < 2', [], 'false'];

        yield ['2 > 2 or 4 < 2', [], 'false'];

        yield ['4 < 2 or 2 < 4', [], 'true'];
    }

    /**
     * {@inheritDoc}
     */
    public static function providePrint(): Generator
    {
        yield from self::providePrintFromEvaluate();
    }
}
