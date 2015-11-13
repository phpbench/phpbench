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

class IterationCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * It should be iterable
     * It sohuld be countable.
     */
    public function testIteration()
    {
        $iterations = new IterationCollection();
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
     * It should calculate the deviation of each iteration from the average.
     */
    public function testComputeDeviation()
    {
        $iterations = new IterationCollection();
        $iterations->replace(array(
            $this->createIteration(4, 50),
            $this->createIteration(8, 0),
            $this->createIteration(4, 50),
            $this->createIteration(16, 100),
        ));

        $iterations->computeDeviations();
    }

    /**
     * It should not crash if compute deviations is called with zero iterations in the collection.
     */
    public function testComputeDeviationZeroIterations()
    {
        $iterations = new IterationCollection();
        $iterations->computeDeviations();
    }

    /**
     * It should mark iterations as rejected if they deviate too far from the mean.
     */
    public function testReject()
    {
        $iterations = new IterationCollection(50);
        $iterations->replace(array(
            $iter1 = $this->createIteration(4, 50),
            $iter2 = $this->createIteration(8, 0),
            $iter3 = $this->createIteration(4, 50),
            $iter4 = $this->createIteration(16, 100),
        ));

        $iterations->computeDeviations();

        $this->assertCount(3, $iterations->getRejects());
        $this->assertContains($iter1, $iterations->getRejects());
        $this->assertContains($iter3, $iterations->getRejects());
        $this->assertContains($iter4, $iterations->getRejects());
        $this->assertNotContains($iter2, $iterations->getRejects());
    }

    /**
     * It should not wait if the number of running processes is less than the concurrnecy.
     */
    public function testWait()
    {
        $iteration1 = $this->prophesize('PhpBench\Benchmark\Iteration');
        $result1 = $this->prophesize('PhpBench\Benchmark\IterationResult');
        $iteration2 = $this->prophesize('PhpBench\Benchmark\Iteration');
        $result2 = $this->prophesize('PhpBench\Benchmark\IterationResult');

        $iteration1->getResult()->willReturn($result1);
        $result1->isReady()->willReturn(false);
        $result1->wait()->shouldNotBeCalled();

        $iteration2->getResult()->willReturn($result2);
        $result2->isReady()->willReturn(true);
        $result2->wait()->shouldNotBeCalled();

        $iterations = new IterationCollection(50, 2);
        $iterations->replace(array(
            $iteration1->reveal(),
            $iteration2->reveal(),
        ));

        $iterations->wait();
    }

    private function createIteration($time, $expectedDeviation = null)
    {
        $iteration = $this->prophesize('PhpBench\Benchmark\Iteration');
        $result = $this->prophesize('PhpBench\Benchmark\IterationResult');
        $result->getTime()->willReturn($time);
        $result->getMemory()->willReturn(null);
        $iteration->getResult()->willReturn($result);

        if (null !== $expectedDeviation) {
            $iteration->setDeviation($expectedDeviation)->shouldBeCalled();
        }

        return $iteration->reveal();
    }
}
