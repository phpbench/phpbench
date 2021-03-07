<?php

namespace PhpBench\Tests\Unit\Expression\Parselet;

use Generator;
use PhpBench\Expression\Ast\DisplayAsNode;
use PhpBench\Expression\Ast\IntegerNode;
use PhpBench\Expression\Ast\UnitNode;
use PhpBench\Tests\Unit\Expression\ParseletTestCase;

class DisplayAsParseletTest extends ParseletTestCase
{
    /**
     * @return Generator<mixed>
     */
    public function provideParse(): Generator
    {
        yield [
            '1 ms as microseconds',
            new DisplayAsNode(new UnitNode(new IntegerNode(1), 'ms'), 'microseconds')
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function provideEvaluate(): Generator
    {
        yield ['1 s as milliseconds', [], '1,000.000ms'];
    }

    /**
     * {@inheritDoc}
     */
    public function providePrint(): Generator
    {
        yield 'units are interpreted literally' => ['1 s as milliseconds', [], '1 s as milliseconds'];

        yield 'int to milliseconds' => ['1000 as milliseconds', [], '1.000ms'];

        yield 'int to bytes' => ['1000 as k', [], '1 k'];

        yield ['100000 as seconds < 1 second', [], '0.100s < 1 second'];

        yield 'default unit' => ['100000 as time < 1 second', [], '100,000.000μs < 1 second'];
    }
}
