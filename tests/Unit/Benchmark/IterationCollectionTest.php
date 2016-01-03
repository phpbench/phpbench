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

use PhpBench\Benchmark\IterationCollection;
use PhpBench\Benchmark\IterationResult;
use Prophecy\Argument;

class IterationCollectionTest extends \PHPUnit_Framework_TestCase
{
    private $subject;
    private $parameterSet;

    public function setUp()
    {
        $this->subject = $this->prophesize('PhpBench\Benchmark\Metadata\SubjectMetadata');
        $this->parameterSet = $this->prophesize('PhpBench\Benchmark\ParameterSet');
    }

    /**
     * It should be iterable
     * It sohuld be countable.
     */
    public function testIteration()
    {
        $iterations = new IterationCollection($this->subject->reveal(), $this->parameterSet->reveal(), 4, 10);

        $this->assertCount(4, $iterations);

        foreach ($iterations as $iteration) {
            $this->assertInstanceOf('PhpBench\Benchmark\Iteration', $iteration);
        }
    }

    /**
     * It should calculate the stats of each iteration from the mean.
     */
    public function testComputeStats()
    {
        $iterations = new IterationCollection($this->subject->reveal(), $this->parameterSet->reveal(), 4, 1);

        $iterations[0]->setResult(new IterationResult(4, null));
        $iterations[1]->setResult(new IterationResult(8, null));
        $iterations[2]->setResult(new IterationResult(4, null));
        $iterations[3]->setResult(new IterationResult(16, null));

        $iterations->computeStats();

        $this->assertEquals(-50, $iterations[0]->getDeviation());
        $this->assertEquals(-0.81649658092772615, $iterations[0]->getZValue());

        $this->assertEquals(0, $iterations[1]->getDeviation());
        $this->assertEquals(0, $iterations[1]->getZValue());

        $this->assertEquals(-50, $iterations[2]->getDeviation());
        $this->assertEquals(-0.81649658092772615, $iterations[2]->getZValue());

        $this->assertEquals(100, $iterations[3]->getDeviation());
        $this->assertEquals(1.6329931618554523, $iterations[3]->getZValue());
    }

    /**
     * It should not crash if compute deviations is called with zero iterations in the collection.
     */
    public function testComputeDeviationZeroIterations()
    {
        $iterations = new IterationCollection($this->subject->reveal(), $this->parameterSet->reveal(), 0, 1);
        $iterations->computeStats();
    }

    /**
     * It should mark iterations as rejected if they deviate too far from the mean.
     */
    public function testReject()
    {
        $iterations = new IterationCollection($this->subject->reveal(), $this->parameterSet->reveal(), 4, 1, 50);

        $iterations[0]->setResult(new IterationResult(4, null));
        $iterations[1]->setResult(new IterationResult(8, null));
        $iterations[2]->setResult(new IterationResult(4, null));
        $iterations[3]->setResult(new IterationResult(16, null));
        $iterations->computeStats();

        $this->assertCount(3, $iterations->getRejects());
        $this->assertContains($iterations[2], $iterations->getRejects());
        $this->assertContains($iterations[3], $iterations->getRejects());
        $this->assertNotContains($iterations[1], $iterations->getRejects());
    }

    private function createIteration($time, $expectedDeviation = null, $expectedZValue = null)
    {
        $iteration = $this->prophesize('PhpBench\Benchmark\Iteration');
        $iteration->getRevolutions()->willReturn(1);
        $iteration->getResult()->willReturn(new IterationResult($time, null));

        if (null !== $expectedDeviation) {
            $iteration->setDeviation($expectedDeviation)->shouldBeCalled();
            if (null === $expectedZValue) {
                $iteration->setZValue(Argument::that(function ($args) use ($expectedZValue) {
                    return round($args[0], 4) == round($expectedZValue, 4);
                }))->shouldBeCalled();
            } else {
                $iteration->setZValue(Argument::any())->shouldBeCalled();
            }
        }

        return $iteration->reveal();
    }

    /**
     * It should be aware of exceptions.
     */
    public function testExceptionAwareness()
    {
        $iterations = new IterationCollection($this->subject->reveal(), $this->parameterSet->reveal(), 4, 1);
        $exception = new \Exception('Test');

        $this->assertFalse($iterations->hasException());
        $iterations->setException($exception);
        $this->assertTrue($iterations->hasException());
        $this->assertSame($exception, $iterations->getException());
    }

    /**
     * It should throw an exception if it is attempted to get an exception when none has been set.
     *
     * @expectedException RuntimeException
     */
    public function testExceptionNoneGet()
    {
        $iterations = new IterationCollection($this->subject->reveal(), $this->parameterSet->reveal(), 4, 1);
        $iterations->getException();
    }

    /**
     * It should throw an exception if getStats is called when no computation has taken place.
     *
     * @expectedException RuntimeException
     * @expectedExceptionMessage No statistics have yet
     */
    public function testGetStatsNoComputeException()
    {
        $iterations = new IterationCollection($this->subject->reveal(), $this->parameterSet->reveal(), 4, 1);
        $iterations->getStats();
    }

    /**
     * It should throw an exception if getStats is called when an exception has been set.
     *
     * @expectedException RuntimeException
     * @expectedExceptionMessage Cannot retrieve stats when an exception
     */
    public function testGetStatsWithExceptionException()
    {
        $iterations = new IterationCollection($this->subject->reveal(), $this->parameterSet->reveal(), 1, 1);
        $iterations[0]->setResult(new IterationResult(4, null));
        $iterations->computeStats();
        $iterations->setException(new \Exception('Test'));
        $iterations->getStats();
    }
}
