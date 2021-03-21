<?php

namespace PhpBench\Tests\Unit\Expression\Parselet;

use Generator;
use PhpBench\Expression\Ast\ArithmeticOperatorNode;
use PhpBench\Expression\Ast\DisplayAsNode;
use PhpBench\Expression\Ast\IntegerNode;
use PhpBench\Expression\Ast\ParameterNode;
use PhpBench\Expression\Ast\StringNode;
use PhpBench\Expression\Ast\UnitNode;
use PhpBench\Expression\Ast\ValueWithUnitNode;
use PhpBench\Expression\NodePrinter\DisplayAsPrinter;
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
            new DisplayAsNode(new ValueWithUnitNode(
                new IntegerNode(1),
                new UnitNode(new StringNode('ms'))
            ), new UnitNode(new StringNode('microseconds')))
        ];

        yield [
            '1 ms as "microseconds"',
            new DisplayAsNode(
                new ValueWithUnitNode(new IntegerNode(1), new UnitNode(new StringNode('ms'))),
                new UnitNode(new StringNode('microseconds'))
            )
        ];

        yield [
            '1 ms as parameter',
            new DisplayAsNode(
                new ValueWithUnitNode(new IntegerNode(1), new UnitNode(new StringNode('ms'))),
                new UnitNode(new ParameterNode(['parameter']))
            )
        ];

        yield [
            '1 ms as parameter * 2',
            new ArithmeticOperatorNode(
                new DisplayAsNode(
                    new ValueWithUnitNode(new IntegerNode(1), new UnitNode(new StringNode('ms'))),
                    new UnitNode(new ParameterNode(['parameter']))
                ),
                '*',
                new IntegerNode(2)
            )
        ];

        yield [
            '1 ms as parameter precision 5',
            new DisplayAsNode(
                new ValueWithUnitNode(new IntegerNode(1), new UnitNode(new StringNode('ms'))),
                new UnitNode(new ParameterNode(['parameter'])),
                new IntegerNode(5)
            )
        ];

        yield [
            '1 ms as parameter precision 5 * 5',
            new ArithmeticOperatorNode(
                new DisplayAsNode(
                    new ValueWithUnitNode(new IntegerNode(1), new UnitNode(new StringNode('ms'))),
                    new UnitNode(new ParameterNode(['parameter'])),
                    new IntegerNode(5)
                ),
                '*',
                new IntegerNode(5)
            )
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function provideEvaluate(): Generator
    {
        yield ['1 s as milliseconds', [], '1,000ms'];
    }

    /**
     * {@inheritDoc}
     */
    public function providePrint(): Generator
    {
        yield 'units are interpreted literally' => ['1 s as milliseconds', [], '1 s as milliseconds'];

        yield 'int to milliseconds' => ['1000 as milliseconds', [], '1ms'];

        yield 'int to bytes' => ['1000 as k', [], '1kb'];

        yield ['100000 as seconds < 1 second', [], '0.100s < 1 second'];

        yield 'default time unit' => ['100000 as time < 1 second', [], '100,000μs < 1 second'];

        yield 'default memory unit' => ['100000 as memory < 1 second', [], '100,000b < 1 second'];
    }
}
