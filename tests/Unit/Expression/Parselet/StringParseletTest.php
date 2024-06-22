<?php

namespace PhpBench\Tests\Unit\Expression\Parselet;

use Generator;
use PhpBench\Expression\Ast\StringNode;
use PhpBench\Tests\Unit\Expression\ParseletTestCase;

class StringParseletTest extends ParseletTestCase
{
    /**
     * @return Generator<mixed>
     */
    public static function provideParse(): Generator
    {
        yield [
            '"1.2"',
            new StringNode("1.2"),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public static function provideEvaluate(): Generator
    {
        yield [
            '"1.2"',
            [],
            '1.2'
        ];
    }

    /**
     * {@inheritDoc}
     */
    public static function providePrint(): Generator
    {
        yield [
            '"1.2"',
            [],
            '1.2',
        ];
    }
}
