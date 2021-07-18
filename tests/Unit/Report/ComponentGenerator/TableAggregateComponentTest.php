<?php

namespace PhpBench\Tests\Unit\Report\ComponentGenerator;

use PhpBench\Data\DataFrame;
use PhpBench\Expression\Ast\ListNode;
use PhpBench\Expression\Ast\StringNode;
use PhpBench\Report\Bridge\ExpressionBridge;
use PhpBench\Report\ComponentGenerator\TableAggregateComponent;
use PhpBench\Report\ComponentGeneratorInterface;
use PhpBench\Report\Model\Builder\TableBuilder;
use PhpBench\Report\Model\Table;

class TableAggregateComponentTest extends ComponentGeneratorTestCase
{
    public function createGenerator(): ComponentGeneratorInterface
    {
        return new TableAggregateComponent($this->container()->get(ExpressionBridge::class));
    }

    public function testNoConfiguration(): void
    {
        $table = $this->generate(DataFrame::empty(), [
        ]);
        assert($table instanceof Table);
        self::assertInstanceOf(Table::class, $table);
        self::assertCount(0, $table->rows());
        self::assertNull($table->headers());
    }

    public function testSetsTitle(): void
    {
        $table = $this->generate(DataFrame::empty(), [
            TableAggregateComponent::PARAM_TITLE => 'Hello',
        ]);
        assert($table instanceof Table);
        self::assertEquals('Hello', $table->title());
    }

    public function testEvaluateRows(): void
    {
        $frame = DataFrame::fromRowSeries([
            ['hello'],
        ], ['foobar']);

        $table = $this->generate($frame, [
            TableAggregateComponent::PARAM_ROW => [
                'hello' => 'partition["foobar"]'
            ],
        ]);
        assert($table instanceof Table);
        self::assertCount(1, $table->rows());
    }

    public function testParition(): void
    {
        $frame = DataFrame::fromRowSeries([
            ['hello'],
            ['goodbye'],
        ], ['foobar']);

        $table = $this->generate($frame, [
            TableAggregateComponent::PARAM_ROW => [
                'hello' => 'partition["foobar"]'
            ],
            TableAggregateComponent::PARAM_PARTITION => ['foobar'],
        ]);
        assert($table instanceof Table);
        self::assertCount(2, $table->rows());
    }

    public function testParitionViaEpression(): void
    {
        $frame = DataFrame::fromRowSeries([
            ['hello'],
            ['goodbye'],
        ], ['foobar']);

        $table = $this->generate($frame, [
            TableAggregateComponent::PARAM_ROW => [
                'hello' => 'partition["foobar"]'
            ],
            TableAggregateComponent::PARAM_PARTITION => 'foobar ~ "foo"',
        ]);
        assert($table instanceof Table);
        self::assertCount(2, $table->rows());
    }

    public function testAccessOuterframe(): void
    {
        $frame = DataFrame::fromRowSeries([
            ['hello'],
            ['goodbye'],
        ], ['foobar']);

        $table = $this->generate($frame, [
            TableAggregateComponent::PARAM_ROW => [
                'hello' => 'partition["foobar"]',
                'goodbye' => 'frame["foobar"]'
            ],
            TableAggregateComponent::PARAM_PARTITION => ['foobar'],
        ]);
        assert($table instanceof Table);
        self::assertCount(2, $table->rows());
        self::assertEquals(TableBuilder::create()->addRowsFromArray([
            [
                'hello' => new ListNode([new StringNode('hello')]),
                'goodbye' => new ListNode([new StringNode('hello'), new StringNode('goodbye')]),
            ],
            [
                'hello' => new ListNode([new StringNode('goodbye')]),
                'goodbye' => new ListNode([new StringNode('hello'), new StringNode('goodbye')]),
            ]
        ])->build(), $table);
    }
}
