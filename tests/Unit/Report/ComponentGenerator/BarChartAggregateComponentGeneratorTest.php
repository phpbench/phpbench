<?php

namespace PhpBench\Tests\Unit\Report\ComponentGenerator;

use PhpBench\Data\DataFrame;
use PhpBench\Expression\ExpressionEvaluator;
use PhpBench\Report\ComponentGenerator\BarChartAggregateComponentGenerator;
use PhpBench\Report\ComponentGeneratorInterface;
use PhpBench\Report\Model\BarChart;

class BarChartAggregateComponentGeneratorTest extends ComponentGeneratorTestCase
{
    public function createGenerator(): ComponentGeneratorInterface
    {
        return new BarChartAggregateComponentGenerator($this->container()->get(ExpressionEvaluator::class));
    }

    public function testMinimalConfiguration(): void
    {
        $barChart = $this->generate(DataFrame::empty(), [
            BarChartAggregateComponentGenerator::PARAM_Y_EXPR => '"hello"',
            BarChartAggregateComponentGenerator::PARAM_Y_ERROR_MARGIN => '12',
        ]);
        assert($barChart instanceof BarChart);
        self::assertInstanceOf(BarChart::class, $barChart);
        self::assertCount(0, $barChart->dataSets());
    }

    public function testXPartitionWithSingleValue(): void
    {
        $barChart = $this->generate(DataFrame::fromRowSeries([
            [ 1 ],
        ], ['col']), [
            BarChartAggregateComponentGenerator::PARAM_X_PARTITION => ['col'],
            BarChartAggregateComponentGenerator::PARAM_Y_EXPR => '10',
        ]);
        assert($barChart instanceof BarChart);
        self::assertInstanceOf(BarChart::class, $barChart);
        self::assertCount(1, $barChart->dataSets());
    }

    public function testGeneratesSeries(): void
    {
        $frame = DataFrame::fromRowSeries([
            ['hello',   12, 33],
            ['goodbye', 23, 44],
        ], ['name', 'value', 'error']);

        $barChart = $this->generate($frame, [
            BarChartAggregateComponentGenerator::PARAM_X_PARTITION => ['name'],
            BarChartAggregateComponentGenerator::PARAM_Y_EXPR => 'first(partition["value"])',
            BarChartAggregateComponentGenerator::PARAM_Y_ERROR_MARGIN => 'first(partition["error"])',
        ]);
        assert($barChart instanceof BarChart);
        self::assertInstanceOf(BarChart::class, $barChart);
        self::assertCount(1, $barChart->dataSets());
        self::assertEquals([12, 23], $barChart->dataSet(0)->ySeries());
        self::assertEquals([33, 44], $barChart->dataSet(0)->errorMargins());
    }

    public function testWithoutErrorMargin(): void
    {
        $frame = DataFrame::fromRowSeries([
            ['hello',   12, 33],
            ['goodbye', 23, 44],
        ], ['name', 'value', 'error']);

        $barChart = $this->generate($frame, [
            BarChartAggregateComponentGenerator::PARAM_X_PARTITION => ['name'],
            BarChartAggregateComponentGenerator::PARAM_Y_EXPR => 'first(partition["value"])',
        ]);
        assert($barChart instanceof BarChart);
        self::assertInstanceOf(BarChart::class, $barChart);
        self::assertCount(1, $barChart->dataSets());
        self::assertEquals([12, 23], $barChart->dataSet(0)->ySeries());
        self::assertNull($barChart->dataSet(0)->errorMargins());
    }

    public function testGeneratesMultipleDataSetes(): void
    {
        $frame = DataFrame::fromRowSeries([
            [1, 'hello',   12, 33],
            [1, 'goodbye', 23, 44],
            [2, 'hello',   22, 33],
            [2, 'goodbye', 43, 54],
        ], ['group', 'name', 'value', 'error']);

        $barChart = $this->generate($frame, [
            BarChartAggregateComponentGenerator::PARAM_X_PARTITION => ['name'],
            BarChartAggregateComponentGenerator::PARAM_SET_PARTITION => ['group'],
            BarChartAggregateComponentGenerator::PARAM_Y_EXPR => 'first(partition["value"])',
            BarChartAggregateComponentGenerator::PARAM_Y_ERROR_MARGIN => 'first(partition["error"])',
        ]);
        assert($barChart instanceof BarChart);
        self::assertInstanceOf(BarChart::class, $barChart);
        self::assertCount(2, $barChart->dataSets());
        self::assertEquals([12, 23], $barChart->dataSet(0)->ySeries());
        self::assertEquals([33, 44], $barChart->dataSet(0)->errorMargins());
        self::assertEquals([22, 43], $barChart->dataSet(1)->ySeries());
        self::assertEquals([33, 54], $barChart->dataSet(1)->errorMargins());
    }

    public function testTitle(): void
    {
        $frame = DataFrame::fromRowSeries([
            [1, 'hello',   12, 33],
            [1, 'goodbye', 23, 44],
            [2, 'hello',   22, 33],
            [2, 'goodbye', 43, 54],
        ], ['group', 'name', 'value', 'error']);

        $barChart = $this->generate($frame, [
            BarChartAggregateComponentGenerator::PARAM_Y_EXPR => 'first(partition["value"])',
            BarChartAggregateComponentGenerator::PARAM_Y_ERROR_MARGIN => 'first(partition["error"])',
            BarChartAggregateComponentGenerator::PARAM_TITLE => 'Hello {{ first(frame["value"])}}',
        ]);
        assert($barChart instanceof BarChart);
        self::assertInstanceOf(BarChart::class, $barChart);
        self::assertEquals('Hello 12', $barChart->title(), 'title');
    }
}
