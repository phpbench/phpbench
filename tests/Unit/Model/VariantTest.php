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

namespace PhpBench\Tests\Unit\Model;

use PhpBench\Model\Iteration;
use PhpBench\Model\ParameterSet;
use PhpBench\Model\Result\ComputedResult;
use PhpBench\Model\Result\MemoryResult;
use PhpBench\Model\Result\TimeResult;
use PhpBench\Model\Subject;
use PhpBench\Model\Variant;
use PhpBench\Tests\Util\TestUtil;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use RuntimeException;

class VariantTest extends TestCase
{
    private $subject;
    private $parameterSet;

    protected function setUp(): void
    {
        $this->subject = $this->prophesize(Subject::class);
        $this->parameterSet = $this->prophesize(ParameterSet::class);
    }

    /**
     * It should spawn variant.
     * It should be iterable
     * It sohuld be countable.
     */
    public function testIterationSpawn()
    {
        $variant = new Variant($this->subject->reveal(), $this->parameterSet->reveal(), 10, 20);
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
        $variant = new Variant($this->subject->reveal(), $this->parameterSet->reveal(), 10, 20);
        $iteration = $variant->createIteration(TestUtil::createResults(10, 20));
        $this->assertInstanceOf('PhpBench\Model\Iteration', $iteration);
        $this->assertEquals(10, $iteration->getResult(TimeResult::class)->getNet());
        $this->assertEquals(20, $iteration->getResult(MemoryResult::class)->getPeak());
        $this->assertEquals(0, $iteration->getIndex());

        $iteration = $variant->createIteration(TestUtil::createResults(10, 20));
        $this->assertEquals(1, $iteration->getIndex());

        $iteration = $variant->createIteration(TestUtil::createResults(10, 20));
        $this->assertEquals(2, $iteration->getIndex());
    }

    /**
     * It should calculate the stats of each iteration from the mean.
     */
    public function testComputeStats()
    {
        $variant = new Variant($this->subject->reveal(), $this->parameterSet->reveal(), 4, 0);
        $this->subject->getRetryThreshold()->willReturn(10);

        $variant->createIteration(TestUtil::createResults(4));
        $variant->createIteration(TestUtil::createResults(8));
        $variant->createIteration(TestUtil::createResults(4));
        $variant->createIteration(TestUtil::createResults(16));

        $variant->computeStats();

        $this->assertEquals(-50, $variant[0]->getResult(ComputedResult::class)->getDeviation());
        $this->assertEquals(-0.81649658092772615, $variant[0]->getResult(ComputedResult::class)->getZValue());

        $this->assertEquals(0, $variant[1]->getResult(ComputedResult::class)->getDeviation());
        $this->assertEquals(0, $variant[1]->getResult(ComputedResult::class)->getZValue());

        $this->assertEquals(-50, $variant[2]->getResult(ComputedResult::class)->getDeviation());
        $this->assertEquals(-0.81649658092772615, $variant[2]->getResult(ComputedResult::class)->getZValue());

        $this->assertEquals(100, $variant[3]->getResult(ComputedResult::class)->getDeviation());
        $this->assertEquals(1.6329931618554523, $variant[3]->getResult(ComputedResult::class)->getZValue());
    }

    /**
     * It should not crash if compute deviations is called with zero variant in the collection.
     */
    public function testComputeDeviationZeroIterations()
    {
        $variant = new Variant($this->subject->reveal(), $this->parameterSet->reveal(), 10, 20);
        $variant->computeStats();
        $this->addToAssertionCount(1);
    }

    /**
     * It should mark variant as rejected if they deviate too far from the mean.
     */
    public function testReject()
    {
        $variant = new Variant($this->subject->reveal(), $this->parameterSet->reveal(), 4, 20);
        $this->subject->getRetryThreshold()->willReturn(10);

        $variant->createIteration(TestUtil::createResults(4));
        $variant->createIteration(TestUtil::createResults(8));
        $variant->createIteration(TestUtil::createResults(4));
        $variant->createIteration(TestUtil::createResults(16));
        $variant->computeStats();

        $this->assertCount(3, $variant->getRejects());
        $this->assertContainsEquals($variant[2], $variant->getRejects());
        $this->assertContainsEquals($variant[3], $variant->getRejects());
        $this->assertNotContainsEquals($variant[1], $variant->getRejects());
    }

    private function createIteration($time, $expectedDeviation = null, $expectedZValue = null)
    {
        $iteration = $this->prophesize(Iteration::class);
        $iteration->getRevolutions()->willReturn(1);
        $iteration->getResult(TimeResult::class)->willReturn(new TimeResult($time));
        $iteration->getResult(MemoryResult::class)->willReturn(new MemoryResult($time));

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
        $variant = new Variant($this->subject->reveal(), $this->parameterSet->reveal(), 10, 20);
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
        $variant = new Variant($this->subject->reveal(), $this->parameterSet->reveal(), 10, 20);
        $errorStack = $variant->getErrorStack();
        $this->assertInstanceOf('PhpBench\Model\ErrorStack', $errorStack);
    }

    /**
     * It should throw an exception if getStats is called when no computation has taken place.
     *
     */
    public function testGetStatsNoComputeException()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No statistics have yet');
        $variant = new Variant($this->subject->reveal(), $this->parameterSet->reveal(), 10, 20);
        $variant->getStats();
    }

    /**
     * It should throw an exception if getStats is called when an exception has been set.
     *
     */
    public function testGetStatsWithExceptionException()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot retrieve stats when an exception');
        $variant = new Variant($this->subject->reveal(), $this->parameterSet->reveal(), 4, 20);
        $this->subject->getRetryThreshold()->willReturn(10);
        $variant->createIteration(TestUtil::createResults(4, 10));
        $variant->createIteration(TestUtil::createResults(4, 10));
        $variant->createIteration(TestUtil::createResults(4, 10));
        $variant->createIteration(TestUtil::createResults(4, 10));
        $variant->computeStats();
        $variant->setException(new \Exception('Test'));
        $variant->getStats();
    }

    /**
     * It should return times and memories.
     */
    public function testGetMetricValues()
    {
        $variant = new Variant($this->subject->reveal(), $this->parameterSet->reveal(), 1, 0);

        $variant->createIteration(TestUtil::createResults(4, 100));
        $variant->createIteration(TestUtil::createResults(8, 200));

        $times = $variant->getMetricValuesByRev(TimeResult::class, 'net');
        $memories = $variant->getMetricValues(MemoryResult::class, 'peak');

        $this->assertEquals([4, 8], $times);
        $this->assertEquals([100, 200], $memories);
    }
}
