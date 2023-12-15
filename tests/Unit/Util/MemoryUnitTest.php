<?php

namespace PhpBench\Tests\Unit\Util;

use Generator;
use PhpBench\Tests\TestCase;
use PhpBench\Util\MemoryUnit;
use RuntimeException;

class MemoryUnitTest extends TestCase
{
    /**
     * @dataProvider provideSiUnits
     * @dataProvider provideBinaryUnits
     */
    public function testConvertToBytes(float $value, string $unit, int $expected): void
    {
        self::assertEquals($expected, MemoryUnit::convertTo($value, $unit, MemoryUnit::BYTES));
    }

    /**
     * @return Generator<mixed>
     */
    public static function provideSiUnits(): Generator
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
                1_000_000
            ];

        yield [
                1,
                MemoryUnit::GIGABYTES,
                1_000_000_000
            ];

        yield [
                2,
                MemoryUnit::GIGABYTES,
                2_000_000_000
            ];
    }

    /**
     * @return Generator<mixed>
     */
    public static function provideBinaryUnits(): Generator
    {
        yield [
                1,
                MemoryUnit::KIBIBYTES,
                2 ** 10
            ];

        yield [
                1,
                MemoryUnit::MEBIBYTES,
                2 ** 20
            ];

        yield [
                1,
                MemoryUnit::GIBIBYTES,
                2 ** 30
            ];
    }

    public function testInvalidUnit(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unknown memory unit');
        MemoryUnit::convertTo(5, 'bobobytes', MemoryUnit::BYTES);
    }


    /**
     * @dataProvider provideSuffix
     */
    public function testSuffix(string $unit, string $expected): void
    {
        self::assertEquals($expected, MemoryUnit::suffixFor($unit));
    }

    /**
     * @return Generator<mixed>
     */
    public static function provideSuffix(): Generator
    {
        yield [
                'megabytes',
                'mb',
            ];

        yield [
                'mb',
                'mb',
            ];

        yield [
                'bytes',
                'b',
            ];
    }

    public function testResolveSuitableUnitWithCustomUnit(): void
    {
        $unit = 'my-unit';

        self::assertEquals($unit, MemoryUnit::resolveSuitableUnit($unit, 1.0));
    }

    /**
     * @dataProvider provideSuitableUnit
     */
    public function testResolveSuitableUnit(float $value, string $expectedUnit): void
    {
        self::assertEquals($expectedUnit, MemoryUnit::resolveSuitableUnit(MemoryUnit::AUTO, $value));
    }

    /**
     * @return Generator<mixed>
     */
    public static function provideSuitableUnit(): Generator
    {
        yield [1, MemoryUnit::BYTES];

        yield [100, MemoryUnit::BYTES];

        yield [1000, MemoryUnit::KILOBYTES];

        yield [10000, MemoryUnit::KILOBYTES];

        yield [100000, MemoryUnit::KILOBYTES];

        yield [1_000_000, MemoryUnit::MEGABYTES];

        yield [1_000_000_000, MemoryUnit::GIGABYTES];
    }

    public function testResolveSuitableBinaryUnitWithCustomUnit(): void
    {
        $unit = 'my-unit';

        self::assertEquals($unit, MemoryUnit::resolveSuitableBinaryUnit($unit, 1.0));
    }

    /**
     * @dataProvider provideSuitableBinaryUnit
     */
    public function testResolveSuitableBinaryUnit(float $value, string $expectedUnit): void
    {
        self::assertEquals($expectedUnit, MemoryUnit::resolveSuitableBinaryUnit(MemoryUnit::AUTO, $value));
    }

    /**
     * @return Generator<mixed>
     */
    public static function provideSuitableBinaryUnit(): Generator
    {
        yield [1, MemoryUnit::BYTES];

        yield [100, MemoryUnit::BYTES];

        yield [1024, MemoryUnit::KIBIBYTES];

        yield [10000, MemoryUnit::KIBIBYTES];

        yield [100000, MemoryUnit::KIBIBYTES];

        yield [1_000_000, MemoryUnit::MEBIBYTES];

        yield [1_000_000_000, MemoryUnit::GIBIBYTES];
    }
}
