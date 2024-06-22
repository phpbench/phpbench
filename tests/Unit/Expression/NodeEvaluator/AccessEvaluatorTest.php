<?php

namespace PhpBench\Tests\Unit\Expression\NodeEvaluator;

use Generator;
use PhpBench\Data\DataFrame;
use PhpBench\Expression\Ast\AccessNode;
use PhpBench\Expression\Ast\DataFrameNode;
use PhpBench\Expression\Ast\IntegerNode;
use PhpBench\Expression\Ast\ListNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\NullNode;
use PhpBench\Expression\Ast\NullSafeNode;
use PhpBench\Expression\Ast\StringNode;
use PhpBench\Tests\Unit\Expression\EvaluatorTestCase;

class AccessEvaluatorTest extends EvaluatorTestCase
{
    /**
     * @dataProvider provideEvaluate
     */
    public function testEvaluate(Node $container, Node $access, Node $expected): void
    {
        self::assertEquals($expected, $this->evaluateNode(new AccessNode(
            $container,
            $access
        ), []));
    }

    /*
     * @return Generator<mixed>
     */
    public static function provideEvaluate(): Generator
    {
        yield [
            new ListNode([
                new StringNode('hello'),
            ]),
            new IntegerNode(0),
            new StringNode('hello')
        ];

        yield [
            new NullSafeNode(
                new ListNode([
                    new StringNode('hello'),
                ])
            ),
            new IntegerNode(100),
            new NullNode()
        ];

        yield [
            new DataFrameNode(DataFrame::fromRecords([
                ['one' => 1],
                ['one' => 2],
            ])),
            new StringNode('one'),
            new ListNode([new IntegerNode(1), new IntegerNode(2)])
        ];

        yield 'null safe data frame' => [
            new NullSafeNode(
                new DataFrameNode(DataFrame::fromRecords([
                    ['one' => 1],
                    ['one' => 2],
                ]))
            ),
            new StringNode('foo'),
            new NullNode()
        ];
    }
}
