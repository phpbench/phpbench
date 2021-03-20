<?php

namespace PhpBench\Tests\Unit\Util;

use Generator;
use PHPUnit\Framework\TestCase;
use PhpBench\Util\UnitConverter;

class UnitConverterTest extends TestCase
{
    /**
     * @dataProvider provideConvert
     */
    public function testConvert(string $from, string $to, float $value, float $expected): void
    {
        self::assertEquals($expected, UnitConverter::convert($from, $to, $value)->value());
    }

    /**
     * @return Generator<mixed>
     */
    public function provideConvert(): Generator
    {
        yield ['milliseconds', 'microseconds', 10, 10000];
        yield ['microseconds', 'milliseconds', 10000, 10];
        yield ['bytes', 'microseconds', 1000, 1000];
    }

    public static function testAllAgainstAll(): void
    {
        foreach (UnitConverter::supportedUnits() as $from) {
            foreach (UnitConverter::supportedUnits() as $to) {
                self::assertIsFloat(UnitConverter::convert($from, $to, 10)->value());
            }
        }
    }

}
