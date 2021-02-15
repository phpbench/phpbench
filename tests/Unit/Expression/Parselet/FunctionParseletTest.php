<?php

namespace PhpBench\Tests\Unit\Expression\Parselet;

use Generator;
use PhpBench\Expression\Ast\BinaryOperatorNode;
use PhpBench\Expression\Ast\FunctionNode;
use PhpBench\Expression\Ast\IntegerNode;
use PhpBench\Tests\Unit\Expression\ParseletTestCase;

class FunctionParseletTest extends ParseletTestCase
{
    /**
     * @return Generator<mixed>
     */
    public function provideParse(): Generator
    {
        yield [
            'foobar()',
            new FunctionNode('foobar', []),
        ];

        yield [
            'foobar(12)',
            new FunctionNode('foobar', [
                new IntegerNode(12)
            ]),
        ];

        yield [
            'foobar(12, 14, 12 + 2)',
            new FunctionNode('foobar', [
                new IntegerNode(12),
                new IntegerNode(14),
                new BinaryOperatorNode(
                    new IntegerNode(12),
                    '+',
                    new IntegerNode(2)
                )
            ]),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function provideEvaluate(): Generator
    {
        yield [
            'mode([12, 12])',
            [],
            12
        ];
    }
}
