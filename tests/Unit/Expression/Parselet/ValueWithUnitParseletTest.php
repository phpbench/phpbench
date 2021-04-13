<?php

namespace PhpBench\Tests\Unit\Expression\Parselet;

use Generator;
use PhpBench\Expression\Ast\IntegerNode;
use PhpBench\Expression\Ast\StringNode;
use PhpBench\Expression\Ast\UnitNode;
use PhpBench\Expression\Ast\ValueWithUnitNode;
use PhpBench\Tests\Unit\Expression\ParseletTestCase;

class ValueWithUnitParseletTest extends ParseletTestCase
{
    /**
     * @return Generator<mixed>
     */
    public function provideParse(): Generator
    {
        yield ['1 ms', new ValueWithUnitNode(new IntegerNode(1), new UnitNode(new StringNode('ms')))];

        yield ['1ms', new ValueWithUnitNode(new IntegerNode(1), new UnitNode(new StringNode('ms')))];
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
