<?php

namespace PhpBench\Tests\Unit\Report\Model;

use PhpBench\Report\Model\BarChart;
use PhpBench\Report\Model\BarChartDataSet;
use PHPUnit\Framework\TestCase;

class BarChartTest extends TestCase
{
    public function testReturnsUniqueXAxesLabels(): void
    {
        $chart = new BarChart([
            new BarChartDataSet('one', [12, 31, 31, 46], [1, 1, 1, 1], [1, 1, 1, 1]),
            new BarChartDataSet('one', [12, 26, 31, 46], [1, 1, 1, 1], [1, 1, 1, 1]),
        ], 'Bar Chart One', 'foo');

        self::assertEquals([12, 26, 31, 46], $chart->xAxes());
    }
}
