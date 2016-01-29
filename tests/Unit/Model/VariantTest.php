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
     * It should spawn variant.
     * It should be iterable
     * It sohuld be countable.
     */
    public function testIterationSpawn()
    {
        $variant = new Variant($this->subject->reveal(), $this->parameterSet->reveal());
        $variant->spawnIterations(4);

        $this->assertCount(4, $variant);

        foreach ($variant as $iteration) {
            $this->assertInstanceOf('PhpBench\Model\Iteration', $iteration);
        }
    }

    /**
     * It should create new iterations with the correct indexes.
     */
    public function testCreateIteration()
    {
        $variant = new Variant($this->subject->reveal(), $this->parameterSet->reveal());
        $iteration = $variant->createIteration(10, 20);
        $this->assertInstanceOf('PhpBench\Model\Iteration', $iteration);
        $this->assertEquals(10, $iteration->getTime());
        $this->assertEquals(20, $iteration->getMemory());
        $this->assertEquals(0, $iteration->getIndex());

        $iteration = $variant->createIteration(10, 20);
        $this->assertEquals(1, $iteration->getIndex());

        $iteration = $variant->createIteration(10, 20);
        $this->assertEquals(2, $iteration->getIndex());
    }

    /**
     * It should calculate the stats of each iteration from the mean.
     */
    public function testComputeStats()
    {
        $variant = new Variant($this->subject->reveal(), $this->parameterSet->reveal());
        $variant->spawnIterations(4);
        $this->subject->getRevs()->willReturn(4);
        $this->subject->getRetryThreshold()->willReturn(10);

        $variant[0]->setResult(new IterationResult(4, null));
        $variant[1]->setResult(new IterationResult(8, null));
        $variant[2]->setResult(new IterationResult(4, null));
        $variant[3]->setResult(new IterationResult(16, null));

        $variant->computeStats();

        $this->assertEquals(-50, $variant[0]->getDeviation());
        $this->assertEquals(-0.81649658092772615, $variant[0]->getZValue());

        $this->assertEquals(0, $variant[1]->getDeviation());
        $this->assertEquals(0, $variant[1]->getZValue());

        $this->assertEquals(-50, $variant[2]->getDeviation());
        $this->assertEquals(-0.81649658092772615, $variant[2]->getZValue());

        $this->assertEquals(100, $variant[3]->getDeviation());
        $this->assertEquals(1.6329931618554523, $variant[3]->getZValue());
    }

    /**
     * It should not crash if compute deviations is called with zero variant in the collection.
     */
    public function testComputeDeviationZeroIterations()
    {
        $variant = new Variant($this->subject->reveal(), $this->parameterSet->reveal());
        $variant->computeStats();
    }

    /**
     * It should mark variant as rejected if they deviate too far from the mean.
     */
    public function testReject()
    {
        $variant = new Variant($this->subject->reveal(), $this->parameterSet->reveal());
        $variant->spawnIterations(4);
        $this->subject->getRevs()->willReturn(4);
        $this->subject->getRetryThreshold()->willReturn(10);

        $variant[0]->setResult(new IterationResult(4, null));
        $variant[1]->setResult(new IterationResult(8, null));
        $variant[2]->setResult(new IterationResult(4, null));
        $variant[3]->setResult(new IterationResult(16, null));
        $variant->computeStats();

        $this->assertCount(3, $variant->getRejects());
        $this->assertContains($variant[2], $variant->getRejects());
        $this->assertContains($variant[3], $variant->getRejects());
        $this->assertNotContains($variant[1], $variant->getRejects());
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
        $variant = new Variant($this->subject->reveal(), $this->parameterSet->reveal());
        $error = new \Exception('Test');

        $this->assertFalse($variant->hasErrorStack());
        $variant->setException($error);
        $this->assertTrue($variant->hasErrorStack());
        $this->assertEquals('Test', $variant->getErrorStack()->getTop()->getMessage());
    }

    /**
     * It should return a new ErrorStack if none has not been set.
     */
    public function testExceptionNoneGet()
    {
        $variant = new Variant($this->subject->reveal(), $this->parameterSet->reveal());
        $errorStack = $variant->getErrorStack();
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
        $variant = new Variant($this->subject->reveal(), $this->parameterSet->reveal());
        $variant->getStats();
    }

    /**
     * It should throw an exception if getStats is called when an exception has been set.
     *
     * @expectedException RuntimeException
     * @expectedExceptionMessage Cannot retrieve stats when an exception
     */
    public function testGetStatsWithExceptionException()
    {
        $variant = new Variant($this->subject->reveal(), $this->parameterSet->reveal());
        $variant->spawnIterations(4);
        $this->subject->getRevs()->willReturn(4);
        $this->subject->getRetryThreshold()->willReturn(10);
        $variant[0]->setResult(new IterationResult(4, null));
        $variant->computeStats();
        $variant->setException(new \Exception('Test'));
        $variant->getStats();
    }
}
