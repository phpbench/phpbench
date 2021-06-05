<?php

namespace PhpBench\Tests\Unit\Report\Model;

use PHPUnit\Framework\TestCase;
use PhpBench\Report\Model\BarChartDataSet;
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
}
