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

use PhpBench\Model\IterationResult;
use PhpBench\Model\Variant;
use Prophecy\Argument;

class VariantTest extends \PHPUnit_Framework_TestCase
{
    private $subject;
    private $parameterSet;

    public function setUp()
    {
        $this->subject = $this->prophesize('PhpBench\Model\Subject');
        $this->parameterSet = $this->prophesize('PhpBench\Model\ParameterSet');
    }

    /**
     * It should be iterable
     * It sohuld be countable.
     */
    public function testIteration()
    {
        $iterations = new Variant($this->subject->reveal(), $this->parameterSet->reveal(), 4, 10, 0);

        $this->assertCount(4, $iterations);

        foreach ($iterations as $iteration) {
            $this->assertInstanceOf('PhpBench\Model\Iteration', $iteration);
        }
    }

    /**
     * It should calculate the stats of each iteration from the mean.
     */
    public function testComputeStats()
    {
        $iterations = new Variant($this->subject->reveal(), $this->parameterSet->reveal(), 4, 1, 0);

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
        $iterations = new Variant($this->subject->reveal(), $this->parameterSet->reveal(), 0, 1, 0);
        $iterations->computeStats();
    }

    /**
     * It should mark iterations as rejected if they deviate too far from the mean.
     */
    public function testReject()
    {
        $iterations = new Variant($this->subject->reveal(), $this->parameterSet->reveal(), 4, 1, 0, 50);

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
        $iteration = $this->prophesize('PhpBench\Model\Iteration');
        $iteration->getRevolutions()->willReturn(1);
        $iteration->getTime()->willReturn($time);
        $iteration->getMemory()->willReturn(null);

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
        $iterations = new Variant($this->subject->reveal(), $this->parameterSet->reveal(), 4, 1, 0);
        $error = new \Exception('Test');

        $this->assertFalse($iterations->hasErrorStack());
        $iterations->setException($error);
        $this->assertTrue($iterations->hasErrorStack());
        $this->assertEquals('Test', $iterations->getErrorStack()->getTop()->getMessage());
    }

    /**
     * It should return a new ErrorStack if none has not been set.
     */
    public function testExceptionNoneGet()
    {
        $iterations = new Variant($this->subject->reveal(), $this->parameterSet->reveal(), 4, 1, 0);
        $errorStack = $iterations->getErrorStack();
        $this->assertInstanceOf('PhpBench\Model\ErrorStack', $errorStack);
    }

    /**
     * It should throw an exception if getStats is called when no computation has taken place.
     *
     * @expectedException RuntimeException
     * @expectedExceptionMessage No statistics have yet
     */
    public function testGetStatsNoComputeException()
    {
        $iterations = new Variant($this->subject->reveal(), $this->parameterSet->reveal(), 4, 1, 0);
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
        $iterations = new Variant($this->subject->reveal(), $this->parameterSet->reveal(), 1, 1, 0);
        $iterations[0]->setResult(new IterationResult(4, null));
        $iterations->computeStats();
        $iterations->setException(new \Exception('Test'));
        $iterations->getStats();
    }
}
