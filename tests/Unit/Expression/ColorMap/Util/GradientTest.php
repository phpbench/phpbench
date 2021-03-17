<?php

namespace PhpBench\Tests\Unit\Expression\ColorMap\Util;

use Generator;
use PHPUnit\Framework\TestCase;
use PhpBench\Expression\ColorMap\Util\Gradient;
use RuntimeException;
use function dechex;

class GradientTest extends TestCase
{
    /**
     * @dataProvider provideGradient
     * @param string[] $expecteds
     */
    public function testGradient(string $start, string $end, int $steps, array $expecteds)
    {
        self::assertEquals($expecteds, (new Gradient())->hexGradient($start, $end, $steps));
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
        $this->gradient()->hexGradient('00000', 'aa0000', 0);
    }

    public function provideNegativeStep(): Generator
    {
        yield [ 0 ];
        yield [ 0.00000001 ];
        yield [ -1 ];
    }

    private function gradient(): Gradient
    {
        return new Gradient();
    }
}
