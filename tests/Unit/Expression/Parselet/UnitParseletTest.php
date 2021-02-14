<?php

namespace PhpBench\Tests\Unit\Expression\Parselet;

use Generator;
use PHPUnit\Framework\TestCase;
use PhpBench\Assertion\ArithmeticNode;
use PhpBench\Expression\Ast\FloatNode;
use PhpBench\Expression\Ast\IntegerNode;
use PhpBench\Expression\Ast\BinaryOperatorNode;
use PhpBench\Expression\Ast\UnitNode;
use PhpBench\Tests\Unit\Expression\ParseletTestCase;

class UnitParseletTest extends ParseletTestCase
{
    /**
     * @return Generator<mixed>
     */
    public function provideParse(): Generator
    {
        yield [
            '1 ms',
            new UnitNode(new IntegerNode(1), 'ms')
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function provideEvaluate(): Generator
    {
        yield ['1s', [], 1E6];
        yield ['1 s', [], 1E6];
    }
}
