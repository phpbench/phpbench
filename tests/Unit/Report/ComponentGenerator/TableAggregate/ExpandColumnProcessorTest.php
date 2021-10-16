<?php

namespace PhpBench\Tests\Unit\Report\ComponentGenerator\TableAggregate;

use PHPUnit\Framework\TestCase;
use PhpBench\Report\Bridge\ExpressionBridge;
use PhpBench\Report\ComponentGenerator\TableAggregate\ColumnProcessorInterface;
use PhpBench\Report\ComponentGenerator\TableAggregate\ExpandColumnProcessor;
use RuntimeException;

class ExpandColumnProcessorTest extends ColumnProcessorTestCase
{
    public function testExpandColumnsEmpty(): void
    {
        self::assertEquals([], $this->processRow([], ['cols' => [],'each' => '',], []));
    }

    public function testExceptionIfEachDoesNotEvaluateToAList(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('column must evaluate to a list');
        self::assertEquals([], $this->processRow([], ['cols' => [],'each' => '"string"',], []));
    }

    public function testEmptyColsWithValidEach(): void
    {
        self::assertEquals([], $this->processRow([], ['cols' => [],'each' => '["string"]',], []));
    }

    public function testPopulatesRow(): void
    {
        self::assertEquals(
            [
                'foo' => 'bar',
            ],
            $this->processRow([], ['cols' => ['foo' => '"bar"'],'each' => '["string"]',], [])
        );
    }

    public function testItemVariableAvailableByDefault(): void
    {
        self::assertEquals(
            [
                'Item: apples' => 'apples',
                'Item: bannanas' => 'bannanas',
            ],
            $this->processRow([], [
                'cols' => ['Item: {{ item }}' => 'item'],
                'each' => '["apples", "bannanas"]'
            ], [])
        );
    }

    public function testCanSpecifyTheParameterName(): void
    {
        self::assertEquals(
            [
                'Item: apples' => 'apples',
                'Item: bannanas' => 'bannanas',
            ],
            $this->processRow([], [
                'param' => 'fruit',
                'cols' => ['Item: {{ fruit }}' => 'fruit'],
                'each' => '["apples", "bannanas"]'
            ], [])
        );
    }

    public function createProcessor(): ColumnProcessorInterface
    {
        return new ExpandColumnProcessor($this->container()->get(ExpressionBridge::class));
    }
}
