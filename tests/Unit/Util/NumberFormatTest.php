<?php

namespace PhpBench\Tests\Unit\Util;

use Generator;
use PHPUnit\Framework\TestCase;
use PhpBench\Util\NumberFormat;

class NumberFormatTest extends TestCase
{
    /**
     * @dataProvider provideFormat
     */
    public function testFormat(float $number, int $decimals, bool $stripTrailingZeros, string $expected): void
    {
        self::assertEquals($expected, NumberFormat::format($number, $decimals, $stripTrailingZeros));
    }

    /**
     * @return Generator<mixed>
     */
    public static function provideFormat(): Generator
    {
        yield [0, 0, true, '0'];

        yield [0, 1, true, '0'];

        yield [0, 2, true, '0'];

        yield [10.12, 2, true, '10.12'];

        yield [10.12, 3, true, '10.12'];

        yield [10.000, 3, true, '10'];

        yield [0, 0, false, '0'];

        yield [0, 1, false, '0.0'];

        yield [0, 2, false, '0.00'];

        yield [10.12, 2, false, '10.12'];

        yield [10.12, 3, false, '10.120'];

        yield [10.000, 3, false, '10.000'];

        yield [1000, 3, true, '1,000'];
    }
}
