<?php

namespace PhpBench\Tests\Unit\Expression\Parselet;

use Generator;
use PhpBench\Expression\Ast\ArithmeticOperatorNode;
use PhpBench\Expression\Ast\DisplayAsNode;
use PhpBench\Expression\Ast\IntegerNode;
use PhpBench\Expression\Ast\StringNode;
use PhpBench\Expression\Ast\UnitNode;
use PhpBench\Expression\Ast\ValueWithUnitNode;
use PhpBench\Expression\Ast\VariableNode;
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
                new UnitNode(new VariableNode('parameter'))
            )
        ];

        yield [
            '1 ms as parameter * 2',
            new ArithmeticOperatorNode(
                new DisplayAsNode(
                    new ValueWithUnitNode(new IntegerNode(1), new UnitNode(new StringNode('ms'))),
                    new UnitNode(new VariableNode('parameter'))
                ),
                '*',
                new IntegerNode(2)
            )
        ];

        yield [
            '1 ms as parameter precision 5',
            new DisplayAsNode(
                new ValueWithUnitNode(new IntegerNode(1), new UnitNode(new StringNode('ms'))),
                new UnitNode(new VariableNode('parameter')),
                new IntegerNode(5)
            )
        ];

        yield [
            '1 ms as parameter precision 5 * 5',
            new ArithmeticOperatorNode(
                new DisplayAsNode(
                    new ValueWithUnitNode(new IntegerNode(1), new UnitNode(new StringNode('ms'))),
                    new UnitNode(new VariableNode('parameter')),
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
        yield ['1 s as milliseconds precision foobar', [
            'foobar' => 2
        ], '1,000.00ms'];

        yield ['1 s as milliseconds', [], '1,000.000ms'];

        yield ['1 s as foobar', [
            'foobar' => 'milliseconds',
        ], '1,000.000ms'];
    }

    /**
     * {@inheritDoc}
     */
    public function providePrint(): Generator
    {
        yield 'units are interpreted literally' => ['1 s as milliseconds', [], '1 s as milliseconds'];

        yield 'int to milliseconds' => ['1000 as milliseconds', [], '1.000ms'];

        yield 'int to milliseconds precision 2' => ['1000 as milliseconds precision 2', [], '1.00ms'];

        yield 'bytes' => ['1000 as bytes', [], '1,000b'];

        yield 'int to bytes' => ['1000 as k', [], '1.000kb'];

        yield 'int to bytes with precision' => ['1000 as k precision 2', [], '1.00kb'];

        yield ['100000 as seconds < 1 second', [], '0.100s < 1 second'];

        yield ['null as seconds', [], 'null'];
    }
}
