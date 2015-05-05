<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench;

class BenchAggregateIterationResultTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->iteration1 = $this->prophesize('PhpBench\BenchIteration');
        $this->iteration2 = $this->prophesize('PhpBench\BenchIteration');

        $this->result = new BenchAggregateIterationResult(array(
            $this->iteration1->reveal(),
            $this->iteration2->reveal(),
        ), array('config' => 'param'));

        $this->iteration1->getTime()->willReturn(10);
        $this->iteration2->getTime()->willReturn(4);
    }

    /**
     * It should return an array of times.
     */
    public function testGetTimes()
    {
        $times = $this->result->getTimes();
        $this->assertEquals(array(
            10, 4,
        ), $times);
    }

    /**
     * It should return the total time.
     */
    public function testGetTotalTime()
    {
        $total = $this->result->getTotalTime();
        $this->assertEquals(14, $total);
    }

    /**
     * It should return the average time.
     */
    public function testGetAverageTime()
    {
        $average = $this->result->getAverageTime();
        $this->assertEquals(7, $average);
    }

    /**
     * It should return the min time.
     */
    public function getMinTime()
    {
        $min = $this->result->getMinTime();
        $this->assertEquals(4, $min);
    }

    /**
     * It should return the max time.
     */
    public function getMaxTime()
    {
        $max = $this->result->getMaxTime();
        $this->assertEquals(10, $max);
    }
}
