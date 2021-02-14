<?php

namespace PhpBench\Tests\Unit\Expression\Parselet;

use Generator;
use PHPUnit\Framework\TestCase;
use PhpBench\Assertion\ArithmeticNode;
use PhpBench\Assertion\Ast\IntegerNode;
use PhpBench\Expression\Ast\BinaryOperatorNode;
use PhpBench\Tests\Unit\Expression\ParseletTestCase;

class BinaryOperatorParseletTest extends ParseletTestCase
{
    /**
     * @return Generator<mixed>
     */
    public function provideParse(): Generator
    {
        yield [
            '1 * 2',
            new BinaryOperatorNode(
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
        yield [
            '1 * 2',
            [],
            2
        ];

        yield [
            '1 + 2',
            [],
            3
        ];

        yield [
            '1 + 2 * 3 / 5 * 6 - 1',
            [],
             1 + 2 * 3 / 5 * 6 - 1
        ];
    }
}
