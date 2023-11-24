<?php

namespace PhpBench\Tests\Unit\Expression\Theme\Util;

use Generator;
use PhpBench\Expression\Theme\Util\Color;
use PhpBench\Expression\Theme\Util\Gradient;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class GradientTest extends TestCase
{
    /**
     * @dataProvider provideGradient
     *
     * @param string[] $expectedColors
     */
    public function testGradient(string $start, string $end, int $steps, array $expectedColors): void
    {
        self::assertEquals(array_map(
            function (string $hex) {
                return Color::fromHex($hex);
            },
            $expectedColors
        ), Gradient::start(
            Color::fromHex($start)
        )->to(
            Color::fromHex($end),
            $steps
        )->toArray());
    }

    /**
     * @return Generator<mixed>
     */
    public static function provideGradient(): Generator
    {
        yield [
            '000000',
            '640000',
            4,
            [
                '000000',
                '190000',
                '320000',
                '4b0000',
                '640000',
            ]
        ];

        yield [
            '000000',
            '640ff0',
            2,
            [
                '000000',
                '320778',
                '640ff0',
            ]
        ];

        yield [
            '000000',
            '640ff0',
            1,
            [
                '000000',
                '640ff0',
            ]
        ];
    }

    /**
     * @dataProvider provideInvalidStep
     *
     * @param int|float $step
     */
    public function testInvalidStep($step): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('more than zero');
        Gradient::start(Color::fromHex('00000'))->to(Color::fromHex('aa0000'), 0);
    }

    /**
     * @return Generator<array<int,int|float>>
     */
    public static function provideInvalidStep(): Generator
    {
        yield [ 0 ];

        yield [ 0.00000001 ];

        yield [ -1 ];
    }

    /**
     * @dataProvider provideColorAtPercentile
     */
    public function testColorAtPercentile(Gradient $gradient, float $percentile, Color $expected): void
    {
        self::assertEquals($expected, $gradient->colorAtPercentile($percentile));
    }

    /**
     * @return Generator<mixed>
     */
    public static function provideColorAtPercentile(): Generator
    {
        yield [
            Gradient::start(Color::fromHex('#000000')),
            1,
            Color::fromHex('#000000')
        ];

        yield [
            Gradient::start(Color::fromRgb(0, 0, 0))->to(Color::fromRgb(0, 0, 10), 10),
            10,
            Color::fromRgb(0, 0, 1)
        ];

        yield [
            Gradient::start(Color::fromRgb(0, 0, 0))->to(Color::fromRgb(0, 0, 10), 9),
            50,
            Color::fromRgb(0, 0, 5)
        ];

        yield [
            Gradient::start(Color::fromRgb(0, 0, 0))->to(Color::fromRgb(0, 0, 10), 9),
            43.5,
            Color::fromRgb(0, 0, 4)
        ];

        yield 'more than 100' => [
            Gradient::start(Color::fromRgb(0, 0, 0))->to(Color::fromRgb(0, 0, 10), 9),
            150,
            Color::fromRgb(0, 0, 10)
        ];

        yield 'less than 100' => [
            Gradient::start(Color::fromRgb(0, 0, 0))->to(Color::fromRgb(0, 0, 10), 9),
            -150,
            Color::fromRgb(0, 0, 0)
        ];
    }
}
