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
        $iterations = new IterationCollection($this->subject->reveal(), $this->parameterSet->reveal());
        $iterations->replace(array(
            $this->createIteration(4),
            $this->createIteration(4),
            $this->createIteration(6),
            $this->createIteration(8),
        ));

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
        $iterations = new IterationCollection($this->subject->reveal(), $this->parameterSet->reveal());
        $iterations->replace(array(
            $this->createIteration(4, -50, -0.70710678118655),
            $this->createIteration(8, 0, 1E-12),
            $this->createIteration(4, -50),
            $this->createIteration(16, 100),
        ));

        $iterations->computeStats();
    }

    /**
     * It should not crash if compute deviations is called with zero iterations in the collection.
     */
    public function testComputeDeviationZeroIterations()
    {
        $iterations = new IterationCollection($this->subject->reveal(), $this->parameterSet->reveal());
        $iterations->computeStats();
    }

    /**
     * It should mark iterations as rejected if they deviate too far from the mean.
     */
    public function testReject()
    {
        $iterations = new IterationCollection($this->subject->reveal(), $this->parameterSet->reveal(), 50);
        $iterations->replace(array(
            $iter1 = $this->createIteration(4, -50),
            $iter2 = $this->createIteration(8, 0),
            $iter3 = $this->createIteration(4, -50),
            $iter4 = $this->createIteration(16, 100),
        ));

        $iterations->computeStats();

        $this->assertCount(3, $iterations->getRejects());
        $this->assertContains($iter1, $iterations->getRejects());
        $this->assertContains($iter3, $iterations->getRejects());
        $this->assertContains($iter4, $iterations->getRejects());
        $this->assertNotContains($iter2, $iterations->getRejects());
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
        $iterations = new IterationCollection($this->subject->reveal(), $this->parameterSet->reveal());
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
        $iterations = new IterationCollection($this->subject->reveal(), $this->parameterSet->reveal());
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
        $iterations = new IterationCollection($this->subject->reveal(), $this->parameterSet->reveal());
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
        $iterations = new IterationCollection($this->subject->reveal(), $this->parameterSet->reveal());
        $iterations->replace(array(
            $this->createIteration(4, 0),
        ));
        $iterations->computeStats();
        $iterations->setException(new \Exception('Test'));
        $iterations->getStats();
    }
}
