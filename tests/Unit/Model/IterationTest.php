<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit\Benchmark;

use PhpBench\Model\Iteration;
use PhpBench\Model\IterationResult;

class IterationTest extends \PHPUnit_Framework_TestCase
{
    private $iteration;
    private $subject;

    public function setUp()
    {
        $this->subject = $this->prophesize('PhpBench\Model\Subject');
        $this->variant = $this->prophesize('PhpBench\Model\Variant');
        $this->variant->getSubject()->willReturn($this->subject->reveal());
        $this->variant->getWarmup()->willReturn(4);
        $this->iteration = new Iteration(
            0,
            $this->variant->reveal()
        );
    }

    /**
     * It should have getters that return its values.
     */
    public function testGetters()
    {
        $this->assertEquals(4, $this->iteration->getWarmup());
    }

    /**
     * It should be possible to set and override the iteration result.
     */
    public function testSetResult()
    {
        $result = new IterationResult(10, 15);
        $this->iteration->setResult($result);
        $this->iteration->setResult($result);
        $this->assertEquals(10, $this->iteration->getTime());
        $this->assertEquals(15, $this->iteration->getMemory());
    }

    /**
     * It should return the revolution time.
     */
    public function testGetRevTime()
    {
        $iteration = new Iteration(1, $this->variant->reveal(), 100);
        $this->variant->getRevolutions()->willReturn(100);
        $this->assertEquals(1, $iteration->getRevTime());
    }
}
