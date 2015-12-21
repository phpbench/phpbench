<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit\Util;

use PhpBench\Util\TimeUnit;

class TimeUnitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * It should convertTo one time unit to another.
     *
     * @dataProvider provideConvert
     */
    public function testConvert($time, $unit, $destUnit, $expectedTime)
    {
        $unit = new TimeUnit($unit, $destUnit);
        $result = $unit->toDestUnit($time);
        $this->assertEquals($expectedTime, $result);
    }

    public function provideConvert()
    {
        return array(
            array(
                60,
                TimeUnit::SECONDS,
                TimeUnit::MINUTES,
                1,
            ),
            array(
                1,
                TimeUnit::SECONDS,
                TimeUnit::MICROSECONDS,
                1000000,
            ),
            array(
                1,
                TimeUnit::SECONDS,
                TimeUnit::MILLISECONDS,
                1000,
            ),
            array(
                24,
                TimeUnit::HOURS,
                TimeUnit::DAYS,
                1,
            ),
            array(
                2.592e+8,
                TimeUnit::MILLISECONDS,
                TimeUnit::DAYS,
                3,
            ),
            array(
                24,
                TimeUnit::HOURS,
                TimeUnit::DAYS,
                1,
            ),
        );
    }

    /**
     * It should convertTo one time unit to another.
     *
     * @dataProvider provideConvertHertz
     */
    public function testConvertHertz($time, $unit, $destUnit, $expectedHertz)
    {
        $unit = new TimeUnit($unit, $destUnit);
        $result = $unit->intoDestUnit($time);
        $this->assertEquals($expectedHertz, $result);
    }

    public function provideConvertHertz()
    {
        return array(
            array(
                1,
                TimeUnit::SECONDS,
                TimeUnit::MINUTES,
                60,
            ),
            array(
                60,
                TimeUnit::SECONDS,
                TimeUnit::MINUTES,
                1,
            ),
            array(
                1,
                TimeUnit::SECONDS,
                TimeUnit::MILLISECONDS,
                0.001,
            ),
            array(
                2,
                TimeUnit::MILLISECONDS,
                TimeUnit::SECONDS,
                500,
            ),
        );
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid time unit "arf"
     */
    public function testInvalidSourceFormat()
    {
        TimeUnit::convertTo(1000, 'arf', TimeUnit::MICROSECONDS);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid time unit "arf"
     */
    public function testInvalidDestFormat()
    {
        TimeUnit::convertTo(1000, TimeUnit::MICROSECONDS, 'arf');
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Expected string value
     */
    public function testInvalidUnitType()
    {
        TimeUnit::convertTo(100, new \stdClass(), TimeUnit::MINUTES);
    }
}
