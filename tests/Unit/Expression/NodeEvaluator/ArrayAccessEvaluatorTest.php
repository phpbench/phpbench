<?php

namespace PhpBench\Tests\Unit\Expression\NodeEvaluator;

use Generator;
use PHPUnit\Framework\TestCase;
use PhpBench\Data\DataFrame;
use PhpBench\Expression\Ast\ArrayAccessNode;
use PhpBench\Expression\Ast\DataFrameNode;
use PhpBench\Expression\Ast\IntegerNode;
use PhpBench\Expression\Ast\ListNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\StringNode;
use PhpBench\Tests\Unit\Expression\EvaluatorTestCase;

class ArrayAccessEvaluatorTest extends EvaluatorTestCase
{
    /**
     * @dataProvider provideEvaluate
     * @param mixed $subject
     */
    public function testEvaluate(Node $container, Node $access, Node $expected): void
    {
        self::assertEquals($expected, $this->evaluateNode(new ArrayAccessNode(
            $container, 
            $access
        ), []));
    }

    /**
     * @return Generator<mixed>
     */
    public function provideEvaluate(): Generator
    {
        yield [
            new ListNode([
                new StringNode('hello'),
            ]),
            new IntegerNode(0),
            new StringNode('hello')
        ];

        yield [
            new DataFrameNode(DataFrame::fromRecords([
                ['one' => 1],
                ['one' => 2],
            ])),
            new StringNode('one'),
            new ListNode([new IntegerNode(1), new IntegerNode(2)])
        ];
    }
}
