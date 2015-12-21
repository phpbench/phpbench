<?php

namespace PhpBench\Tests\Unit\Util;

use PhpBench\Util\TimeUnit;
use PhpBench\Util\TimeFormatter;

class TimeFormatterTest extends \PHPUnit_Framework_TestCase
{
    private $formatter;

    public function setUp()
    {
        $this->formatter = new TimeFormatter(new TimeUnit(TimeUnit::MICROSECONDS, TimeUnit::MICROSECONDS));
    }

    /**
     * It should format time
     * 
     * @dataProvider provideFormatTime
     */
    public function testFormatTime($time, $mode, $unit, $expected)
    {
        $result = $this->formatter->format($time, $mode, $unit);
        $this->assertEquals($expected, $result);
    }

    public function provideFormatTime()
    {
        return array(
            array(
                500000,
                TimeFormatter::MODE_THROUGHPUT,
                TimeUnit::SECONDS,
                '2ops/s'
            ),
            array(
                500,
                TimeFormatter::MODE_THROUGHPUT,
                TimeUnit::SECONDS,
                '2000ops/s'
            ),
            array(
                1,
                TimeFormatter::MODE_THROUGHPUT,
                TimeUnit::MILLISECONDS,
                '1000ops/ms'
            )
        );
    }
}
