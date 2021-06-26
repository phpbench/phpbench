<?php

namespace PhpBench\Tests\Unit\Report\Model;

use PhpBench\Report\Model\BarChartDataSet;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class BarChartDataSetTest extends TestCase
{
    public function testExceptionWhenXSeriesAndYSeriesAreNotSameSize(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('equal number');
        new BarChartDataSet('test', [1, 2], [1], []);
    }

    public function testExceptionWhenErrorMarginsNotSameSize(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('equal number');
        new BarChartDataSet('test', [], [], [12]);
    }

    public function testExceptionWhenErrorMarginsNotSameSizeWithNullErrorMargin(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('equal number');
        new BarChartDataSet('test', [1], [], null);
    }
}
