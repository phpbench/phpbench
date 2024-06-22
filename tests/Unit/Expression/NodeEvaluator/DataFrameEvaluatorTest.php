<?php

namespace PhpBench\Tests\Unit\Expression\NodeEvaluator;

use Generator;
use PhpBench\Data\DataFrame;
use PhpBench\Expression\Ast\ComparisonNode;
use PhpBench\Expression\Ast\DataFrameNode;
use PhpBench\Expression\Ast\ListNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\StringNode;
use PhpBench\Expression\Ast\VariableNode;
use PhpBench\Expression\NodeEvaluator\DataFrameEvaluator;
use PhpBench\Tests\Unit\Expression\EvaluatorTestCase;

class DataFrameEvaluatorTest extends EvaluatorTestCase
{
    /**
     * @dataProvider provideEvaluate
     */
    public function testEvaluate(DataFrame $frame, Node $accessNode, Node $expected): void
    {
        $evaluator = new DataFrameEvaluator();
        self::assertEquals(
            $expected,
            $evaluator->evaluate($this->evaluator(), new DataFrameNode($frame), $accessNode, [], false)
        );
    }

    /**
     * @return Generator<mixed>
     */
    public static function provideEvaluate(): Generator
    {
        yield 'column access' => [
            DataFrame::fromRecords([
                ['foo' => 'bar']
            ]),
            new StringNode('foo'),
            new ListNode([new StringNode('bar')])
        ];

        yield 'filter expression' => [
            DataFrame::fromRecords([
                ['foo' => 'bar'],
                ['foo' => 'baz'],
            ]),
            new ComparisonNode(new VariableNode('foo'), '=', new StringNode('bar')),
            new DataFrameNode(DataFrame::fromRecords([
                ['foo' => 'bar'],
            ])),
        ];
    }
}
