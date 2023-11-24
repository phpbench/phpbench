<?php

namespace PhpBench\Tests\Unit\Expression\Parselet;

use Generator;
use PhpBench\Expression\Ast\AccessNode;
use PhpBench\Expression\Ast\ArgumentListNode;
use PhpBench\Expression\Ast\FunctionNode;
use PhpBench\Expression\Ast\IntegerNode;
use PhpBench\Expression\Ast\ListNode;
use PhpBench\Expression\Ast\ParenthesisNode;
use PhpBench\Expression\Ast\StringNode;
use PhpBench\Expression\Ast\VariableNode;
use PhpBench\Tests\Unit\Expression\ParseletTestCase;

class PropertyAccessParseletTest extends ParseletTestCase
{
    /**
     * @return Generator<mixed>
     */
    public static function provideParse(): Generator
    {
        yield [
            '([10]).foobar',
            new AccessNode(
                new ParenthesisNode(
                    new ListNode([
                        new IntegerNode(10)
                    ])
                ),
                new StringNode('foobar')
            ),
        ];

        yield [
            'first(frame).barfoo',
            new AccessNode(
                new FunctionNode(
                    'first',
                    new ArgumentListNode([
                        new VariableNode('frame')
                    ])
                ),
                new StringNode("barfoo")
            ),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public static function provideEvaluate(): Generator
    {
        yield ['first(data).foobar', [
            'data' => [
                ['foobar' => 10],
            ],
        ], '10'];

        yield ['first(data)["foobar"].barfoo', [
            'data' => [
                ['foobar' => ['barfoo' => 10]],
            ],
        ], '10'];
    }

    /**
     * {@inheritDoc}
     */
    public static function providePrint(): Generator
    {
        yield [
            'first([[10, 20]]).foobar',
            [],
            'first([[10, 20]])[foobar]'
        ];

        yield ['first([[10, 20]])[foobar]'];
    }
}
