<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace PhpBench\Tests\Unit\Model\Result;

use PhpBench\Model\Result\TimeResult;
use PHPUnit\Framework\TestCase;

class TimeResultTest extends TestCase
{
    public function testGetters()
    {
        $result = new TimeResult(10);
        $this->assertEquals(10, $result->getNet());
    }

    /**
     * It should allow time "0" (because windows mostly).
     */
    public function testTimeZero()
    {
        $result = new TimeResult(0);
        $this->assertEquals(0, $result->getNet());
    }

    /**
     * It should throw an exception if the time is less than 0.
     *
     */
    public function testLessThanZero()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Time cannot be less than 0, got -10');
        new TimeResult(-10);
    }

    /**
     * It should throw an exception if the revolutions is zero0 or less than zero.
     *
     */
    public function testRevsAreZero()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Revolutions must be more than 0, got 0');
        $result = new TimeResult(10);
        $result->getRevTime(0);
    }

    /**
     * It should return the rev time.
     */
    public function testGetRevTime()
    {
        $result = new TimeResult(10);
        $revTime = $result->getRevTime(2);

        $this->assertEquals(5, $revTime);
    }
}
