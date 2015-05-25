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
use PhpBench\Benchmark;
use PhpBench\Benchmark\Iteration;

class RunnerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->logger = $this->prophesize('PhpBench\\ProgressLogger');
        $this->collectionBuilder = $this->prophesize('PhpBench\\Benchmark\\CollectionBuilder');
        $this->subjectBuilder = $this->prophesize('PhpBench\\Benchmark\\SubjectBuilder');
        $this->case = new RunnerTestBenchCase();
        $this->collection = $this->prophesize('PhpBench\\Benchmark\\Collection');
        $this->subject = $this->prophesize('PhpBench\\Benchmark\\Subject');

        $this->runner = new Runner(
            $this->collectionBuilder->reveal(), $this->subjectBuilder->reveal(),
            $this->logger->reveal()
        );
    }

    /**
     * It should run the tests.
     *
     * - With 1 iteration, 1 revolution and with two parameter providers
     * - With 1 iteration, 4 revolution and zero parameter providers
     * - With 1 iteration, 4 iterations and one parameter provider
     *
     * @dataProvider provideRunner
     */
    public function testRunner($iterations, $revs, $paramProviders, $expectedNbCalls)
    {
        $this->collectionBuilder->buildCollection()->willReturn($this->collection);
        $this->collection->getBenchmarks()->willReturn(array(
            $this->case,
        ));
        $this->subjectBuilder->buildSubjects($this->case)->willReturn(array(
            $this->subject->reveal(),
        ));
        $this->subject->getNbIterations()->willReturn($iterations);
        $this->subject->getParameterProviders()->willReturn($paramProviders);
        $this->subject->getMethodName()->willReturn('benchFoo');
        $this->subject->getBeforeMethods()->willReturn(array('beforeFoo'));
        $this->subject->getDescription()->willReturn('Hello world');
        $this->subject->getProcessIsolation()->willReturn(false);
        $this->subject->getRevs()->willReturn($revs);

        $result = $this->runner->runAll();

        $this->assertEquals($expectedNbCalls, $this->case->called);
        $this->assertTrue($this->case->beforeCalled);

        $this->assertInstanceOf('PhpBench\\Result\\SuiteResult', $result);
        $this->assertEquals(1, count($result->getBenchmarkResults()));
    }

    public function provideRunner()
    {
        return array(
            array(
                1,
                array(1),
                array(
                    'paramSetOne', 'paramSetTwo'
                ),
                2
            ),
            array(
                1,
                array(1, 3),
                array(),
                4
            ),
            array(
                1,
                array(1, 3),
                array('paramSetTwo'),
                4
            )
        );
    }

    /**
     * It should throw an exception if a before method does not exist.
     *
     * @expectedException PhpBench\Exception\InvalidArgumentException
     * @expectedExceptionMessage Unknown bench benchmark method "beforeFooNotExisting"
     */
    public function testInvalidBeforeMethod()
    {
        $this->collectionBuilder->buildCollection()->willReturn($this->collection);
        $this->collection->getBenchmarks()->willReturn(array(
            $this->case,
        ));
        $this->subjectBuilder->buildSubjects($this->case)->willReturn(array(
            $this->subject->reveal(),
        ));
        $this->subject->getNbIterations()->willReturn(1);
        $this->subject->getParameterProviders()->willReturn(array());
        $this->subject->getBeforeMethods()->willReturn(array('beforeFooNotExisting'));
        $this->subject->getProcessIsolation()->willReturn(false);
        $this->subject->getRevs()->willReturn(array(1));

        $this->runner->runAll();
    }

    /**
     * It should throw an exception if a parameter provider does not exist.
     *
     * @expectedException PhpBench\Exception\InvalidArgumentException
     * @expectedExceptionMessage Unknown param provider "notExistingParam" for bench benchmark
     */
    public function testInvalidParamProvider()
    {
        $this->collectionBuilder->buildCollection()->willReturn($this->collection);
        $this->collection->getBenchmarks()->willReturn(array(
            $this->case,
        ));
        $this->subjectBuilder->buildSubjects($this->case)->willReturn(array(
            $this->subject->reveal(),
        ));
        $this->subject->getNbIterations()->willReturn(1);
        $this->subject->getParameterProviders()->willReturn(array('notExistingParam'));
        $this->subject->getProcessIsolation()->willReturn(false);

        $this->runner->runAll();
    }

    /**
     * The magic tearDown and setUp should be called.
     */
    public function testSetUpAndTearDown()
    {
        $this->collectionBuilder->buildCollection()->willReturn($this->collection);
        $this->collection->getBenchmarks()->willReturn(array(
            $this->case,
        ));
        $this->subjectBuilder->buildSubjects($this->case)->willReturn(array(
            $this->subject->reveal(),
        ));
        $this->subject->getParameterProviders()->willReturn(array());
        $this->subject->getMethodName()->willReturn('benchFoo');
        $this->subject->getDescription()->willReturn('benchFoo');
        $this->subject->getNbIterations()->willReturn(0);
        $this->subject->getProcessIsolation()->willReturn(false);
        $this->subject->getRevs()->willReturn(array(1));

        $this->runner->runAll();

        $this->assertTrue($this->case->setUpCalled);
        $this->assertTrue($this->case->tearDownCalled);
    }

    /**
     * The magic tearDown and setUp should not be called if setUpTearDown is false.
     */
    public function testSetUpAndTearDownDisabled()
    {
        $runner = new Runner(
            $this->collectionBuilder->reveal(), $this->subjectBuilder->reveal(),
            $this->logger->reveal(),
            false,
            false
        );

        $this->collectionBuilder->buildCollection()->willReturn($this->collection);
        $this->collection->getBenchmarks()->willReturn(array(
            $this->case,
        ));
        $this->subjectBuilder->buildSubjects($this->case)->willReturn(array(
            $this->subject->reveal(),
        ));
        $this->subject->getParameterProviders()->willReturn(array());
        $this->subject->getMethodName()->willReturn('benchFoo');
        $this->subject->getDescription()->willReturn('benchFoo');
        $this->subject->getNbIterations()->willReturn(0);
        $this->subject->getProcessIsolation()->willReturn(false);
        $this->subject->getRevs()->willReturn(array(1));

        $runner->runAll();

        $this->assertFalse($this->case->setUpCalled);
        $this->assertFalse($this->case->tearDownCalled);
    }
}

class RunnerTestBenchCase implements Benchmark
{
    public $beforeCalled = false;
    public $setUpCalled = false;
    public $tearDownCalled = false;
    public $called = 0;

    public function setUp()
    {
        $this->setUpCalled = true;
    }

    public function tearDown()
    {
        $this->tearDownCalled = true;
    }

    public function paramSetOne()
    {
        return array(
            array('foo' => 'bar'),
            array('foo' => 'bar'),
        );
    }

    public function beforeFoo(Iteration $iteration)
    {
        $this->beforeCalled = true;
    }

    public function paramSetTwo()
    {
        return array(
            array('bar' => 'foo'),
        );
    }

    public function benchFoo(Iteration $iteration)
    {
        $this->called++;
    }
}
