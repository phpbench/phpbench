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

use PhpBench\Benchmark\Iteration;

class IterationTest extends \PHPUnit_Framework_TestCase
{
    private $iteration;
    private $subject;

    public function setUp()
    {
        $this->subject = $this->prophesize('PhpBench\Benchmark\Metadata\SubjectMetadata');
        $this->iteration = new Iteration(
            0,
            $this->subject->reveal(),
            5,
            array()
        );
    }

    /**
     * It should throw an exception something tries to retrieve the result before it has been set.
     *
     * @expectedException RuntimeException
     * @expectedExceptionMessage iteration result
     */
    public function testGetResultNotSet()
    {
        $this->iteration->getResult();
    }

    /**
     * It should be possible to set and override the iteration result.
     */
    public function testSetResult()
    {
        $result = $this->prophesize('PhpBench\Benchmark\IterationResult');
        $this->iteration->setResult($result->reveal());
        $this->iteration->setResult($result->reveal());
        $this->assertSame($result->reveal(), $this->iteration->getResult());
    }
}
