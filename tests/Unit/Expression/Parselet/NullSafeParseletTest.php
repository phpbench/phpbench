<?php

namespace PhpBench\Tests\Unit\Expression\Parselet;

use Generator;
use PhpBench\Expression\Ast\AccessNode;
use PhpBench\Expression\Ast\IntegerNode;
use PhpBench\Expression\Ast\NullSafeNode;
use PhpBench\Expression\Ast\VariableNode;
use PhpBench\Tests\Unit\Expression\ParseletTestCase;

class NullSafeParseletTest extends ParseletTestCase
{
    /**
     * @return Generator<mixed>
     */
    public function provideParse(): Generator
    {
        yield [
            'foobar?[0]',
            new AccessNode(
                new NullSafeNode(
                    new VariableNode('foobar')
                ),
                new IntegerNode(0)
            ),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function provideEvaluate(): Generator
    {
        yield [
            'foobar[0]',
            [
                'foobar' => [1],
            ],
            '1'
        ];

        yield [
            'foobar?[2]',
            [
                'foobar' => [],
            ],
            'null'
        ];

        yield [
            'foobar["bar"]?[2]',
            [
                'foobar' => [
                    'bar' => [1, 2, 3]
                ],
            ],
            '3'
        ];

        yield [
            'foobar["bar"]?[2]',
            [
                'foobar' => [
                    'bar' => []
                ],
            ],
            'null'
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function providePrint(): Generator
    {
        yield [
            'foobar?[1]',
            [],
            'foobar?[1]',
        ];

        yield [
            'foobar?[1]?[2]',
            [],
            'foobar?[1]?[2]',
        ];
    }
}
