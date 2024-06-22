<?php

namespace PhpBench\Tests\Unit\Report\ComponentGenerator\TableAggregate;

use PhpBench\Data\DataFrame;
use PhpBench\Report\Bridge\ExpressionBridge;
use PhpBench\Report\ComponentGenerator\TableAggregate\ColumnProcessorInterface;
use PhpBench\Report\ComponentGenerator\TableAggregate\ExpandColumnProcessor;
use RuntimeException;

class ExpandColumnProcessorTest extends ColumnProcessorTestCase
{
    public function testExpandColumnsEmpty(): void
    {
        self::assertEquals([], $this->processRow(
            ['cols' => [],'partition' => '',],
            DataFrame::empty(),
            []
        ));
    }

    public function testEmptyColsWithValidEach(): void
    {
        self::assertEquals([], $this->processRow(
            ['cols' => [],'partition' => '["string"]',],
            DataFrame::empty(),
            []
        ));
    }

    public function testPopulatesRow(): void
    {
        self::assertEquals(
            [
                'foo' => 'bar',
            ],
            $this->processRow(
                ['cols' => ['foo' => '"bar"'],'partition' => 'c1',],
                DataFrame::fromRowSeries([
                    [ 'v1', 'v2' ],
                ], ['c1', 'c2']),
                []
            )
        );
    }

    public function testItemVariableAvailableByDefault(): void
    {
        self::assertEquals(
            [
                'Item: apples' => 'apples',
                'Item: bannanas' => 'bannanas',
            ],
            $this->processRow(
                ['cols' => ['Item: {{ first(partition["fruit"]) }}' => 'first(partition["fruit"])'], 'partition' => ['fruit']],
                DataFrame::fromRowSeries([
                    [ 'apples' ],
                    [ 'bannanas' ],
                ], ['fruit']),
                []
            )
        );
    }

    public function testCanSpecifyTheParameterName(): void
    {
        self::assertEquals(
            [
                'Item: apples' => 'apples',
                'Item: bannanas' => 'bannanas',
            ],
            $this->processRow(
                [
                    'var' => 'item',
                    'cols' => [
                        'Item: {{ first(item["fruit"]) }}' => 'first(item["fruit"])'
                    ],
                    'partition' => ['fruit']
                ],
                DataFrame::fromRowSeries([
                    [ 'apples' ],
                    [ 'bannanas' ],
                ], ['fruit']),
                []
            )
        );
    }

    public function testCanUsePairtionKey(): void
    {
        self::assertEquals(
            [
                'Item: apples' => 'apples',
                'Item: bannanas' => 'bannanas',
            ],
            $this->processRow(
                [
                    'partition' => ['fruit'],
                    'cols' => [
                        'Item: {{ key }}' => 'first(partition["fruit"])'
                    ],
                ],
                DataFrame::fromRowSeries([
                    [ 'apples' ],
                    [ 'bannanas' ],
                ], ['fruit']),
                []
            )
        );
    }

    public function testCanSpecifyPairtionKey(): void
    {
        self::assertEquals(
            [
                'Item: apples' => 'apples',
                'Item: bannanas' => 'bannanas',
            ],
            $this->processRow(
                [
                    'partition' => ['fruit'],
                    'key_var' => 'foobar',
                    'cols' => [
                        'Item: {{ foobar }}' => 'first(partition["fruit"])'
                    ],
                ],
                DataFrame::fromRowSeries([
                    [ 'apples' ],
                    [ 'bannanas' ],
                ], ['fruit']),
                []
            )
        );
    }

    public function testThrowsExceptionIfColumnNameIsDuplicated(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('already been set');
        self::assertEquals(
            [
                'Item: apples' => 'apples',
                'Item: bannanas' => 'bannanas',
            ],
            $this->processRow(
                [
                    'partition' => ['fruit'],
                    'key_var' => 'foobar',
                    'cols' => [
                        'item' => 'first(partition["fruit"])'
                    ],
                ],
                DataFrame::fromRowSeries([
                    [ 'apples' ],
                    [ 'bannanas' ],
                ], ['fruit']),
                []
            )
        );
    }

    public function createProcessor(): ColumnProcessorInterface
    {
        return new ExpandColumnProcessor($this->container()->get(ExpressionBridge::class));
    }
}
