<?php

namespace PhpBench\Tests\Unit\Expression\Parselet;

use Generator;
use PhpBench\Expression\Ast\IntegerNode;
use PhpBench\Expression\Ast\UnitNode;
use PhpBench\Tests\Unit\Expression\ParseletTestCase;

class UnitParseletTest extends ParseletTestCase
{
    /**
     * @return Generator<mixed>
     */
    public function provideParse(): Generator
    {
        yield ['1 ms', new UnitNode(new IntegerNode(1), 'ms')];

        yield ['1ms', new UnitNode(new IntegerNode(1), 'ms')];
    }

    /**
     * {@inheritDoc}
     */
    public function provideEvaluate(): Generator
    {
        yield ['1 s', [], (string)1E6];
    }

    /**
     * {@inheritDoc}
     */
    public function providePrint(): Generator
    {
        yield from $this->providePrintFromEvaluate();
    }
}
