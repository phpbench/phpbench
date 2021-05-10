<?php

namespace PhpBench\Tests\Unit\Report;

use PHPUnit\Framework\TestCase;
use PhpBench\Data\DataFrame;

class DataFrameTest extends TestCase
{
    public function testFromRecords(): void
    {
        $records = [
            [
                'one' => 1,
                'two' => 2,
            ],
            [
                'one' => 3,
                'two' => 4,
            ],
        ];
        self::assertSame($records, DataFrame::fromRecords($records)->toRecords());
    }

    public function testExceptionWithInconsistentRecords(): void
    {
        $this->expectExceptionMessage('Record "1" was expected to have columns "one", but it has "one", "two"');
        $records = [
            [
                'one' => 1,
            ],
            [
                'one' => 3,
                'two' => 4,
            ],
        ];
        DataFrame::fromRecords($records)->toRecords();
    }

    public function testReturnColumnValues(): void
    {
        $records = [
            [
                'one' => 1,
                'two' => 4,
            ],
            [
                'one' => 3,
                'two' => 4,
            ],
        ];
        self::assertEquals([1, 3], DataFrame::fromRecords($records)->column('one')->toValues());
    }

    public function testExceptionOnInvalidColumn(): void
    {
        $this->expectExceptionMessage('Could not find column "three", known columns "one", "two"');
        $records = [
            [
                'one' => 1,
                'two' => 4,
            ],
        ];
        self::assertEquals([1, 3], DataFrame::fromRecords($records)->column('three')->toValues());
    }

    public function testReturnRowValues(): void
    {
        $records = [
            [
                'one' => 1,
                'two' => 4,
            ],
            [
                'one' => 3,
                'two' => 4,
            ],
        ];
        self::assertEquals([1, 4], DataFrame::fromRecords($records)->row(0)->toValues());
    }

    public function testExceptionOnInvalidRow(): void
    {
        $this->expectExceptionMessage('Could not find row "5" in data frame with 1 row(s)');
        $records = [
            [
                'one' => 1,
                'two' => 4,
            ],
        ];
        self::assertEquals([1, 3], DataFrame::fromRecords($records)->row(5)->toValues());
    }

    public function testColumnValues(): void
    {
        $records = [
            [
                'one' => 1,
                'two' => 4,
            ],
            [
                'one' => 3,
                'two' => 4,
            ],
        ];
        self::assertEquals([
            'one' => [1, 3],
            'two' => [4, 4],
        ], DataFrame::fromRecords($records)->row(0)->columnValues());
    }
}
