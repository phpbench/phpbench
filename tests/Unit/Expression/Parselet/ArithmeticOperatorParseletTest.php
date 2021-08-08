<?php

namespace PhpBench\Tests\Unit\Expression\Parselet;

use Generator;
use PhpBench\Expression\Ast\ArithmeticOperatorNode;
use PhpBench\Expression\Ast\IntegerNode;
use PhpBench\Tests\Unit\Expression\ParseletTestCase;

class ArithmeticOperatorParseletTest extends ParseletTestCase
{
    /**
     * @return Generator<mixed>
     */
    public function provideParse(): Generator
    {
        yield [
            '1 * 2',
            new ArithmeticOperatorNode(
                new IntegerNode(1),
                '*',
                new IntegerNode(2)
            )
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function provideEvaluate(): Generator
    {
        yield ['1 * 2', [], '2'];

        yield ['1 + 2', [], '3'];

        yield ['1 + 2 * 3 / 5 * 6 - 1', [], (string)(1 + 2 * 3 / 5 * 6 - 1)];

        yield ['foo + foo', [
            'foo' => 1,
        ], '2'];

        yield ['foo[0] + foo[0] * foo[0]', [
            'foo' => [1],
        ], '2'];
    }

    /**
     * {@inheritDoc}
     */
    public function providePrint(): Generator
    {
        yield from $this->providePrintFromEvaluate();
    }
}
