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
use PhpBench\Benchmark\IterationResult;
use PhpBench\Benchmark\ParameterSet;

class IterationTest extends \PHPUnit_Framework_TestCase
{
    private $iteration;
    private $subject;

    public function setUp()
    {
        $this->subject = $this->prophesize('PhpBench\Benchmark\Metadata\SubjectMetadata');
        $this->collection = $this->prophesize('PhpBench\Benchmark\IterationCollection');
        $this->collection->getSubject()->willReturn($this->subject->reveal());
        $this->iteration = new Iteration(
            0,
            $this->collection->reveal(),
            5,
            new ParameterSet()
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
        $result = new IterationResult(10, 10);
        $this->iteration->setResult($result);
        $this->iteration->setResult($result);
        $this->assertSame($result, $this->iteration->getResult());
    }
}
