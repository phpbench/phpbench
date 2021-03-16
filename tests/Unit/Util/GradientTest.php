<?php

namespace PhpBench\Tests\Unit\Util;

use Generator;
use PHPUnit\Framework\TestCase;
use PhpBench\Util\Gradient;
use function dechex;

class GradientTest extends TestCase
{
    /**
     * @dataProvider provideGradient
     */
    public function testGradient(string $start, string $end, int $steps, array $expecteds)
    {
        self::assertEquals($expecteds, Gradient::calculateHex($start, $end, $steps));
    }

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
    }
}
