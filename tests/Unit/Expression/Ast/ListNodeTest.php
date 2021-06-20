<?php

namespace PhpBench\Tests\Unit\Expression\Ast;

use Generator;
use PhpBench\Expression\Ast\IntegerNode;
use PhpBench\Expression\Ast\ListNode;
use PHPUnit\Framework\TestCase;

class ListNodeTest extends TestCase
{
    /**
     * @dataProvider provideValues
     *
     * @param parameters $values
     */
    public function testFromValues(array $values, ListNode $expected): void
    {
        $node = ListNode::fromValues($values);
        self::assertEquals($expected, $node, 'Node');
        self::assertEquals($values, $node->value(), 'To Array');
    }

    /**
     * @return Generator<mixed>
     */
    public function provideValues(): Generator
    {
        yield [
            [],
            new ListNode()
        ];

        yield [
            [1],
            new ListNode([new IntegerNode(1)]),
        ];

        yield [
            [1,2],
            new ListNode([new IntegerNode(1), new IntegerNode(2)])
        ];

        yield [
            [1,2,3],
            new ListNode([new IntegerNode(1), new IntegerNode(2), new IntegerNode(3)])
        ];

        yield [
            [1,2,3,4,5],
            new ListNode([
                new IntegerNode(1),
                new IntegerNode(2),
                new IntegerNode(3),
                new IntegerNode(4),
                new IntegerNode(5)
            ])
        ];

        yield [
            [1,2,[3,4,5]],
            new ListNode([
                new IntegerNode(1),
                new IntegerNode(2),
                new ListNode([
                    new IntegerNode(3),
                    new IntegerNode(4),
                    new IntegerNode(5)
                ])
            ])
        ];
    }
}
