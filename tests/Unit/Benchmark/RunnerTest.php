<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Benchmark;

use PhpBench\Benchmark\Runner;
use PhpBench\Benchmark\RunnerContext;
use PhpBench\Environment\Information;
use PhpBench\Model\Iteration;
use PhpBench\Model\IterationResult;
use PhpBench\Model\Subject;
use PhpBench\Model\Suite;
use PhpBench\PhpBench;
use PhpBench\Registry\Config;
use PhpBench\Tests\Util\TestUtil;
use Prophecy\Argument;

class RunnerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->benchmarkFinder = $this->prophesize('PhpBench\Benchmark\BenchmarkFinder');
        $this->suite = $this->prophesize('PhpBench\Model\Suite');
        $this->subject = $this->prophesize('PhpBench\Model\Subject');
        $this->benchmark = $this->prophesize('PhpBench\Model\Benchmark');
        $this->benchmarkFinder->findBenchmarks(__DIR__, array(), array())->willReturn(array(
            $this->benchmark->reveal(),
        ));
        $this->executor = $this->prophesize('PhpBench\Benchmark\ExecutorInterface');
        $this->executorRegistry = $this->prophesize('PhpBench\Registry\Registry');
        $this->executorConfig = new Config('test', array('executor' => 'microtime'));
        $this->envSupplier = $this->prophesize('PhpBench\Environment\Supplier');
        $this->informations = new \ArrayObject();
        $this->envSupplier->getInformations()->willReturn($this->informations);

        $this->runner = new Runner(
            $this->benchmarkFinder->reveal(),
            $this->executorRegistry->reveal(),
            $this->envSupplier->reveal(),
            null,
            null
        );

        $this->executorRegistry->getService('microtime')->willReturn(
            $this->executor->reveal()
        );
        $this->executorRegistry->getConfig('microtime')->willReturn(
            $this->executorConfig
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
    public function testRunner($iterations, $revs, array $parameters, $assertionCallbacks)
    {
        $subject = new Subject($this->benchmark->reveal(), 'name', 0);
        $subject->setIterations($iterations);
        $subject->setBeforeMethods(array('beforeFoo'));
        $subject->setParameterSets(array(array($parameters)));
        $subject->setRevs($revs);

        TestUtil::configureBenchmark($this->benchmark);

        $this->benchmark->getSubjects()->willReturn(array(
            $subject,
        ));

        $this->executor->execute(
            Argument::type('PhpBench\Model\Iteration'),
            new Config('test', array(
                'executor' => 'microtime',
            ))
        )
            ->shouldBeCalledTimes(count($revs) * $iterations)
            ->willReturn(new IterationResult(10, 10));

        $suite = $this->runner->run(new RunnerContext(__DIR__, array(
            'context_name' => 'context',
        )));

        $this->assertInstanceOf('PhpBench\Model\Suite', $suite);
        $this->assertNoErrors($suite);

        foreach ($assertionCallbacks as $callback) {
            $callback($this, $suite);
        }
    }

    public function provideRunner()
    {
        return array(
            array(
                1,
                1,
                array(),
                function ($test, $suite) {
                    $test->assertEquals(1, $suite->getSummary()->getNbIterations());
                },
            ),
            array(
                1,
                3,
                array(),
                function ($test, $suite) {
                    $test->assertEquals(1, $iterations = $suite->getIterations());
                    $iteration = reset($iterations);
                    $test->assertEquals(3, $iteration->getVariant()->getRevolutions());
                },
            ),
            array(
                4,
                3,
                array(),
                function ($test, $suite) {
                    $test->assertEquals(4, $iterations = $suite->getIterations());
                    $iteration = reset($iterations);
                    $test->assertEquals(3, $iteration->getVariant()->getRevolutions());
                },
            ),
            array(
                1,
                1,
                array('one' => 'two', 'three' => 'four'),
                function ($test, $suite) {
                    $test->assertEquals(1, $iterations = $suite->getIterations());
                    $iteration = reset($iterations);
                    $parameters = $iteration->getVariant()->getParameterSet();
                    $test->assertEquals('two', $parameters['one']);
                    $test->assertEquals('four', $parameters['four']);
                },
            ),
            array(
                1,
                1,
                array('one', 'two'),
                function ($test, $suite) {
                    $test->assertEquals(1, $iterations = $suite->getIterations());
                    $iteration = reset($iterations);
                    $parameters = $iteration->getVariant()->getParameterSet();
                    $test->assertEquals('one', $parameters[0]);
                    $test->assertEquals('two', $parameters[1]);
                },
            ),
            array(
                1,
                1,
                array('one' => array('three' => 'four')),
                function ($test, $suite) {
                    $test->assertEquals(1, $iterations = $suite->getIterations());
                    $iteration = reset($iterations);
                    $parameters = $iteration->getVariant()->getParameterSet();
                    $test->assertEquals(array('three', 'four'), $parameters[0]);
                },
            ),
        );
    }

    /**
     * It should skip subjects that should be skipped.
     */
    public function testSkip()
    {
        $subject = new Subject($this->benchmark->reveal(), 'name', 0);
        $subject->setSkip(true);
        $this->benchmark->getSubjects()->willReturn(array(
            $subject,
        ));
        TestUtil::configureBenchmark($this->benchmark);
        $suite = $this->runner->run(new RunnerContext(__DIR__));

        $this->assertInstanceOf('PhpBench\Model\Suite', $suite);
        $this->assertNoErrors($suite);
        $this->assertEquals(0, $suite->getSummary()->getNbSubjects());
    }

    /**
     * It should set the sleep attribute in the DOM.
     */
    public function testSleep()
    {
        $subject = new Subject($this->benchmark->reveal(), 'name', 0);
        $subject->setSleep(50);
        $this->benchmark->getSubjects()->willReturn(array(
            $subject,
        ));
        TestUtil::configureBenchmark($this->benchmark);

        $test = $this;
        $this->executor->execute(Argument::type('PhpBench\Model\Iteration'), $this->executorConfig)
            ->will(function ($args) use ($test) {
                $iteration = $args[0];
                $test->assertEquals(50, $iteration->getVariant()->getSubject()->getSleep());

                return new IterationResult(10, 10);
            });

        $suite = $this->runner->run(new RunnerContext(__DIR__));
        $this->assertNoErrors($suite);
    }

    /**
     * It should allow the sleep value to be overridden.
     */
    public function testSleepOverride()
    {
        $subject = new Subject($this->benchmark->reveal(), 'name', 0);
        $subject->setSleep(50);
        $this->benchmark->getSubjects()->willReturn(array(
            $subject,
        ));
        TestUtil::configureBenchmark($this->benchmark);

        $test = $this;
        $this->executor->execute(Argument::type('PhpBench\Model\Iteration'), $this->executorConfig)
            ->will(function ($args) use ($test) {
                $iteration = $args[0];
                $test->assertEquals(100, $iteration->getVariant()->getSubject()->getSleep());

                return new IterationResult(10, 10);
            });

        $suite = $this->runner->run(new RunnerContext(__DIR__, array(
            'sleep' => 100,
        )));
        $this->assertNoErrors($suite);
    }

    /**
     * It should set the warmup attribute in the DOM.
     */
    public function testWarmup()
    {
        $subject = new Subject($this->benchmark->reveal(), 'name', 0);
        $subject->setWarmup(50);
        $this->benchmark->getSubjects()->willReturn(array(
            $subject,
        ));
        TestUtil::configureBenchmark($this->benchmark);

        $test = $this;
        $this->executor->execute(Argument::type('PhpBench\Model\Iteration'), $this->executorConfig)
            ->will(function ($args) use ($test) {
                $iteration = $args[0];
                $test->assertEquals(50, $iteration->getVariant()->getSubject()->getWarmup());

                return new IterationResult(10, 10);
            });

        $suite = $this->runner->run(new RunnerContext(__DIR__, array()));

        $this->assertInstanceOf('PhpBench\Model\Suite', $suite);
        $this->assertNoErrors($suite);
    }

    /**
     * It should serialize the retry threshold.
     */
    public function testRetryThreshold()
    {
        $subject = new Subject($this->benchmark->reveal(), 'name', 0);
        $this->benchmark->getSubjects()->willReturn(array(
            $subject,
        ));
        TestUtil::configureBenchmark($this->benchmark);

        $test = $this;
        $this->executor->execute(Argument::type('PhpBench\Model\Iteration'), $this->executorConfig)
            ->will(function ($args) use ($test) {
                $iteration = $args[0];
                $test->assertEquals(10, $iteration->getVariant()->getRetryThreshold());

                return new IterationResult(10, 10);
            });

        $suite = $this->runner->run(new RunnerContext(
            __DIR__,
            array(
                'retry_threshold' => 10,
            )
        ));

        $this->assertInstanceOf('PhpBench\Model\Suite', $suite);
    }

    /**
     * It should call the before and after class methods.
     */
    public function testBeforeAndAfterClass()
    {
        TestUtil::configureBenchmark($this->benchmark, array(
            'beforeClassMethods' => array('afterClass'),
            'afterClassMethods' => array('beforeClass'),
        ));

        $this->executor->executeMethods($this->benchmark->reveal(), array('beforeClass'))->shouldBeCalled();
        $this->executor->executeMethods($this->benchmark->reveal(), array('afterClass'))->shouldBeCalled();
        $this->benchmark->getSubjects()->willReturn(array());

        $this->runner->run(new RunnerContext(__DIR__));
    }

    /**
     * It should handle exceptions thrown by the executor.
     * It should handle nested exceptions.
     */
    public function testHandleExceptions()
    {
        $subject = new Subject($this->benchmark->reveal(), 'name', 0);
        $this->benchmark->getSubjects()->willReturn(array(
            $subject,
        ));
        TestUtil::configureBenchmark($this->benchmark);

        $this->executor->execute(Argument::type('PhpBench\Model\Iteration'), $this->executorConfig)
            ->shouldBeCalledTimes(1)
            ->willThrow(new \Exception('Foobar', null, new \InvalidArgumentException('Barfoo')));

        $suite = $this->runner->run(new RunnerContext(
            __DIR__
        ));

        $errorStacks = $suite->getErrorStacks();
        $this->assertCount(1, $errorStacks);
        $errorStack = iterator_to_array(reset($errorStacks));
        $this->assertCount(2, $errorStack);
        $this->assertEquals('Exception', $errorStack[0]->getClass());
        $this->assertEquals('InvalidArgumentException', $errorStack[1]->getClass());
        $this->assertEquals('Foobar', $errorStack[0]->getMessage());
        $this->assertEquals('Barfoo', $errorStack[1]->getMessage());
    }

    /**
     * It should add environmental information to the DOM.
     */
    public function testEnvironment()
    {
        $this->informations[] = new Information('hello', array('say' => 'goodbye'));

        $subject = new Subject($this->benchmark->reveal(), 'name', 0);
        $this->benchmark->getSubjects()->willReturn(array(
            $subject,
        ));

        TestUtil::configureBenchmark($this->benchmark);
        $this->executor->execute(Argument::type('PhpBench\Model\Iteration'), $this->executorConfig)
            ->shouldBeCalledTimes(1)
            ->willReturn(new IterationResult(10, 10));
        $suite = $this->runner->run(new RunnerContext(
            __DIR__
        ));
        $envInformations = $suite->getEnvInformations();
        $this->assertSame($this->informations, $envInformations);
    }

    private function assertNoErrors(Suite $suite)
    {
        if ($errorStacks = $suite->getErrorStacks()) {
            $errorStack = reset($errorStacks);
            $this->fail('Runner encountered error: ' . $errorStack->getTop()->getMessage());
        }

        if ($errorStacks = $suite->getErrorStacks()) {
            $errorStack = reset($errorStacks);
            $this->fail('Runner encountered error: ' . $errorStack->getTop()->getMessage());
        }
    }
}

class RunnerTestBenchCase
{
}
