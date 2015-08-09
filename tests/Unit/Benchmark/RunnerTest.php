<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Benchmark;

use PhpBench\Benchmark\Runner;
use PhpBench\Benchmark\Iteration;
use PhpBench\BenchmarkInterface;

class RunnerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->collectionBuilder = $this->prophesize('PhpBench\\Benchmark\\CollectionBuilder');
        $this->subjectBuilder = $this->prophesize('PhpBench\\Benchmark\\SubjectBuilder');
        $this->case = new RunnerTestBenchCase();
        $this->collection = $this->prophesize('PhpBench\\Benchmark\\Collection');
        $this->subject = $this->prophesize('PhpBench\\Benchmark\\Subject');
        $this->collectionBuilder->buildCollection(__DIR__)->willReturn($this->collection);
        $this->executor = $this->prophesize('PhpBench\Benchmark\Executor');

        $this->runner = new Runner(
            $this->collectionBuilder->reveal(), 
            $this->subjectBuilder->reveal(),
            $this->executor->reveal(),
            null
        );
    }

    /**
     * It should run the tests.
     *
     * - With 1 iteration, 1 revolution
     * - With 1 iteration, 4 revolutions
     *
     * @dataProvider provideRunner
     */
    public function testRunner($iterations, $revs, $expectedNbIterations)
    {
        $this->collection->getBenchmarks()->willReturn(array(
            $this->case,
        ));
        $this->subjectBuilder->buildSubjects($this->case, null, null, null)->willReturn(array(
            $this->subject->reveal(),
        ));
        $this->subject->getNbIterations()->willReturn($iterations);
        $this->subject->getMethodName()->willReturn('benchFoo');
        $this->subject->getBeforeMethods()->willReturn(array('beforeFoo'));
        $this->subject->getAfterMethods()->willReturn(array());
        $this->subject->getIdentifier()->willReturn(1);
        $this->subject->getParamProviders()->willReturn(array());
        $this->subject->getGroups()->willReturn(array());
        $this->subject->getRevs()->willReturn($revs);

        foreach ($revs as $revCount) {
            $this->executor->execute($this->case, 'benchFoo', $revCount, array('beforeFoo'), array(), array())->shouldBeCalledTimes($iterations);
        }

        $result = $this->runner->runAll(__DIR__);

        $this->assertInstanceOf('PhpBench\Benchmark\SuiteDocument', $result);
        $this->assertEquals($expectedNbIterations, $result->getNbIterations());
    }

    public function provideRunner()
    {
        return array(
            array(
                1,
                array(1),
                1
            ),
            array(
                1,
                array(1, 3),
                2,
            ),
            array(
                4,
                array(1, 3),
                8
            ),
        );
    }
}

class RunnerTestBenchCase implements BenchmarkInterface
{
}
