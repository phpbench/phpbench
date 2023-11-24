<?php

namespace PhpBench\Tests\Unit\Expression\Parselet;

use Generator;
use PhpBench\Expression\Ast\NullNode;
use PhpBench\Tests\Unit\Expression\ParseletTestCase;

class NullParseletTest extends ParseletTestCase
{
    /**
     * @return Generator<mixed>
     */
    public static function provideParse(): Generator
    {
        yield [
            'null',
            new NullNode(),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public static function provideEvaluate(): Generator
    {
        yield [
            'null',
            [],
            'null'
        ];
    }

    /**
     * {@inheritDoc}
     */
    public static function providePrint(): Generator
    {
        yield [
            'null',
            [],
            'null',
        ];
    }
}
