<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench;

class BenchRunnerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->logger = $this->prophesize('PhpBench\\BenchProgressLogger');
        $this->finder = $this->prophesize('PhpBench\\BenchFinder');
        $this->subjectBuilder = $this->prophesize('PhpBench\\BenchSubjectBuilder');
        $this->case = new BenchRunnerTestBenchCase();
        $this->collection = $this->prophesize('PhpBench\\BenchCaseCollection');
        $this->subject = $this->prophesize('PhpBench\\BenchSubject');

        $this->runner = new BenchRunner(
            $this->finder->reveal(),
            $this->subjectBuilder->reveal(),
            $this->logger->reveal()
        );
    }

    /**
     * It should run the tests.
     */
    public function testRunner()
    {
        $iterations = 1;

        $this->finder->buildCollection()->willReturn($this->collection);
        $this->collection->getCases()->willReturn(array(
            $this->case,
        ));
        $this->subjectBuilder->buildSubjects($this->case)->willReturn(array(
            $this->subject->reveal(),
        ));
        $this->subject->getNbIterations()->willReturn($iterations);
        $this->subject->getParamProviders()->willReturn(array(
            'paramSetOne',
            'paramSetTwo',
        ));
        $this->subject->getMethodName()->willReturn('benchFoo');
        $this->subject->getBeforeMethods()->willReturn(array('beforeFoo'));

        $result = $this->runner->runAll();

        $this->assertTrue($this->case->called);
        $this->assertTrue($this->case->beforeCalled);

        $this->assertInstanceOf('PhpBench\\BenchCaseCollectionResult', $result);
        $this->assertEquals(1, count($result->getCaseResults()));
    }
}

class BenchRunnerTestBenchCase implements BenchCase
{
    public $called = false;
    public $beforeCalled = false;

    public function paramSetOne()
    {
        return array(
            array('foo' => 'bar'),
            array('foo' => 'bar'),
        );
    }

    public function beforeFoo(BenchIteration $iteration)
    {
        $this->beforeCalled = true;
    }

    public function paramSetTwo()
    {
        return array(
            array('bar' => 'foo'),
        );
    }

    public function benchFoo(BenchIteration $iteration)
    {
        $this->called = true;
    }
}
