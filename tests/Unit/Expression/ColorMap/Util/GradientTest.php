<?php

namespace PhpBench\Tests\Unit\Expression\ColorMap\Util;

use Generator;
use PHPUnit\Framework\TestCase;
use PhpBench\Expression\ColorMap\Util\Color;
use PhpBench\Expression\ColorMap\Util\Gradient;
use RuntimeException;
use function dechex;

class GradientTest extends TestCase
{
    /**
     * @dataProvider provideGradient
     * @param string[] $expecteds
     */
    public function testGradient(string $start, string $end, int $steps, array $expecteds): void
    {
        self::assertEquals(array_map(
            function (string $hex) {return Color::fromHex($hex);},
            $expecteds
        ), Gradient::start(Color::fromHex($start))->to(Color::fromHex($end), $steps)->toArray());
    }

    /**
     * @return Generator<mixed>
     */
    public function provideGradient(): Generator
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
     * @dataProvider provideNegativeStep
     */
    public function testNegativeStep($step): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectErrorMessage('more than zero');
        Gradient::start(Color::fromHex('00000'), 0)->to(Color::fromHex('aa0000'), 0);
    }

    public function provideNegativeStep(): Generator
    {
        yield [ 0 ];
        yield [ 0.00000001 ];
        yield [ -1 ];
    }
}
