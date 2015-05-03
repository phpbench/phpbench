<?php

namespace PhpBench;

use Prophecy\Argument;
use PhpBench\BenchCase;

class BenchRunnerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->logger = $this->prophesize('PhpBench\\BenchProgressLogger');
        $this->generator = $this->prophesize('PhpBench\\BenchReportGenerator');
        $this->finder = $this->prophesize('PhpBench\\BenchFinder');
        $this->subjectBuilder = $this->prophesize('PhpBench\\BenchSubjectBuilder');
        $this->case = new BenchRunnerTestBenchCase();
        $this->collection = $this->prophesize('PhpBench\\BenchCaseCollection');
        $this->subject = $this->prophesize('PhpBench\\BenchSubject');
        $this->matrixBuilder = $this->prophesize('PhpBench\\BenchMatrixBuilder');

        $this->runner = new BenchRunner(
            $this->finder->reveal(),
            $this->subjectBuilder->reveal(),
            $this->logger->reveal(),
            array($this->generator),
            $this->matrixBuilder->reveal()
        );
    }

    /**
     * It should run something
     */
    public function testRunner()
    {
        $iterations = 1;

        $this->finder->buildCollection()->willReturn($this->collection);
        $this->collection->getCases()->willReturn(array(
            $this->case
        ));
        $this->subjectBuilder->buildSubjects($this->case)->willReturn(array(
            $this->subject->reveal()
        ));
        $this->subject->getNbIterations()->willReturn($iterations);
        $this->subject->getParamProviders()->willReturn(array(
            'paramSetOne',
            'paramSetTwo',
        ));
        $this->subject->getMethodName()->willReturn('benchFoo');
        $this->subject->getBeforeMethods()->willReturn(array('beforeFoo'));
        $this->subject->addIteration(Argument::type('PhpBench\\BenchIteration'))->shouldBeCalled();
        $this->matrixBuilder->buildMatrix(
            array(
                array(
                    array('foo' => 'bar'),
                    array('foo' => 'bar'),
                ),
                array(
                    array('bar' => 'foo'),
                ),
            )
        )->willReturn(
            array(
                'barfoo' => 'foobar',
            )
        );

        $this->runner->runAll();

        $this->assertTrue($this->case->called);
        $this->assertTrue($this->case->beforeCalled);
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
