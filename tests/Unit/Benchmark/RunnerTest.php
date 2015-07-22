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
        $this->collectionBuilder->buildCollection(__DIR__)->willReturn($this->collection);

        $this->runner = new Runner(
            $this->collectionBuilder->reveal(), $this->subjectBuilder->reveal(),
            $this->logger->reveal()
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
    public function testRunner($iterations, $revs, $expectedNbCalls)
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
        $this->subject->getDescription()->willReturn('Hello world');
        $this->subject->getIdentifier()->willReturn(1);
        $this->subject->getParameters()->willReturn(array());
        $this->subject->getGroups()->willReturn(array());
        $this->subject->getProcessIsolation()->willReturn(false);
        $this->subject->getRevs()->willReturn($revs);

        $result = $this->runner->runAll(__DIR__);

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
                1,
            ),
            array(
                1,
                array(1, 3),
                4,
            ),
            array(
                1,
                array(1, 3),
                4,
            ),
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
        $this->collection->getBenchmarks()->willReturn(array(
            $this->case,
        ));
        $this->subjectBuilder->buildSubjects($this->case, null, null, null)->willReturn(array(
            $this->subject->reveal(),
        ));
        $this->subject->getNbIterations()->willReturn(1);
        $this->subject->getIdentifier()->willReturn(1);
        $this->subject->getParameters()->willReturn(array());
        $this->subject->getBeforeMethods()->willReturn(array('beforeFooNotExisting'));
        $this->subject->getProcessIsolation()->willReturn(false);
        $this->subject->getRevs()->willReturn(array(1));

        $this->runner->runAll(__DIR__);
    }

    /**
     * The magic tearDown and setUp should be called.
     */
    public function testSetUpAndTearDown()
    {
        $this->collection->getBenchmarks()->willReturn(array(
            $this->case,
        ));
        $this->subjectBuilder->buildSubjects($this->case, null, null, null)->willReturn(array(
            $this->subject->reveal(),
        ));
        $this->subject->getIdentifier()->willReturn(1);
        $this->subject->getParameters()->willReturn(array());
        $this->subject->getMethodName()->willReturn('benchFoo');
        $this->subject->getDescription()->willReturn('benchFoo');
        $this->subject->getGroups()->willReturn(array());
        $this->subject->getNbIterations()->willReturn(0);
        $this->subject->getProcessIsolation()->willReturn(false);
        $this->subject->getRevs()->willReturn(array(1));

        $this->runner->runAll(__DIR__);

        $this->assertTrue($this->case->setUpCalled);
        $this->assertTrue($this->case->tearDownCalled);
    }

    /**
     * The magic tearDown and setUp should not be called if setUpTearDown is false.
     */
    public function testSetUpAndTearDownDisabled()
    {
        $this->runner->disableSetup();

        $this->collection->getBenchmarks()->willReturn(array(
            $this->case,
        ));
        $this->subjectBuilder->buildSubjects($this->case, null, null, null)->willReturn(array(
            $this->subject->reveal(),
        ));
        $this->subject->getIdentifier()->willReturn(1);
        $this->subject->getParameters()->willReturn(array());
        $this->subject->getMethodName()->willReturn('benchFoo');
        $this->subject->getDescription()->willReturn('benchFoo');
        $this->subject->getGroups()->willReturn(array());
        $this->subject->getNbIterations()->willReturn(0);
        $this->subject->getProcessIsolation()->willReturn(false);
        $this->subject->getRevs()->willReturn(array(1));

        $this->runner->runAll(__DIR__);

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
