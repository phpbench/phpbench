<?php

namespace PhpBench\Tests\Unit\Util;

use Generator;
use PhpBench\Tests\TestCase;
use PhpBench\Util\MemoryUnit;
use RuntimeException;

class MemoryUnitTest extends TestCase
{
    /**
     * @dataProvider provideConvertToBytes
     */
    public function testConvertToBytes(float $value, string $unit, int $expected): void
    {
        self::assertEquals($expected, MemoryUnit::convertTo($value, $unit, MemoryUnit::BYTES));
    }
        
    /**
     * @return Generator<mixed>
     */
    public function provideConvertToBytes(): Generator
    {
        yield [
                1,
                MemoryUnit::BYTES,
                1
            ];

        yield [
                1,
                MemoryUnit::KILOBYTES,
                1000
            ];

        yield [
                1,
                MemoryUnit::MEGABYTES,
                1000000
            ];

        yield [
                1,
                MemoryUnit::GIGABYTES,
                1000000000
            ];

        yield [
                2,
                MemoryUnit::GIGABYTES,
                2000000000
            ];
    }

    public function testInvalidUnit(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unknown memory unit');
        MemoryUnit::convertTo(5, 'bobobytes', MemoryUnit::BYTES);
    }
}
