<?php

namespace PhpBench\Tests\Unit\Formatter\Format;

use Generator;
use PhpBench\Formatter\Format\InvertOnThroughputFormat;
use PhpBench\Tests\TestCase;
use PhpBench\Util\TimeUnit;

class InvertOnThroughputFormatTest extends TestCase
{
    /**
     * @dataProvider provideFormat
     */
    public function testFormat(array $options, string $subject, string $expected): void
    {
        self::assertEquals($expected, (new InvertOnThroughputFormat(new TimeUnit()))->format($subject, $options));
    }

    /**
     * @return Generator<mixed>
     */
    public function provideFormat(): Generator
    {
        yield [
            [
                'mode' => TimeUnit::MODE_TIME,
            ],
            '10',
            '10',
        ];

        yield [
            [
                'mode' => TimeUnit::MODE_THROUGHPUT,
            ],
            '10',
            '-10',
        ];
    }
}
