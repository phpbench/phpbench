<?php

namespace PhpBench\Tests\Unit\Util;

use Generator;
use PhpBench\Util\TextTruncate;
use PHPUnit\Framework\TestCase;

class TextTruncateTest extends TestCase
{
    /**
     * @dataProvider provideCenter
     */
    public function testCenter(string $input, int $center, int $length, string $expected): void
    {
        self::assertEquals($expected, TextTruncate::centered($input, $center, '...', $length));
    }

    /**
     * @return Generator<mixed>
     */
    public static function provideCenter(): Generator
    {
        yield [
            '12345678',
            5,
            2,
            '... 4567 ...'
        ];

        yield ['1234567', 5, 2, '... 4567'];

        yield ['', 5, 2, ''];

        yield ['1234', 2, 2, '1234'];
    }
}
