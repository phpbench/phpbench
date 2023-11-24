<?php

namespace PhpBench\Tests\Unit\Expression\Parselet;

use Generator;
use PhpBench\Expression\Ast\IntegerNode;
use PhpBench\Tests\Unit\Expression\ParseletTestCase;

class IntegerParseletTest extends ParseletTestCase
{
    /**
     * @return Generator<mixed>
     */
    public static function provideParse(): Generator
    {
        yield [
            '1',
            new IntegerNode(1),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public static function provideEvaluate(): Generator
    {
        yield [
            '1',
            [],
            '1'
        ];
    }

    /**
     * {@inheritDoc}
     */
    public static function providePrint(): Generator
    {
        yield from self::providePrintFromEvaluate();
    }
}
