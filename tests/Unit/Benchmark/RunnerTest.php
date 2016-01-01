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

use PhpBench\Benchmark\Iteration;
use PhpBench\Benchmark\IterationResult;
use PhpBench\Benchmark\Runner;
use PhpBench\Benchmark\RunnerContext;
use PhpBench\PhpBench;
use PhpBench\Tests\Util\TestUtil;
use Prophecy\Argument;

class RunnerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->collectionBuilder = $this->prophesize('PhpBench\Benchmark\CollectionBuilder');
        $this->collection = $this->prophesize('PhpBench\Benchmark\Collection');
        $this->subject = $this->prophesize('PhpBench\Benchmark\Metadata\SubjectMetadata');
        $this->collectionBuilder->buildCollection(__DIR__, array(), array())->willReturn($this->collection);
        $this->executor = $this->prophesize('PhpBench\Benchmark\ExecutorInterface');
        $this->registry = $this->prophesize('PhpBench\Benchmark\Executor\Registry');
        $this->benchmark = $this->prophesize('PhpBench\Benchmark\Metadata\BenchmarkMetadata');

        $this->runner = new Runner(
            $this->collectionBuilder->reveal(),
            $this->registry->reveal(),
            null,
            null
        );

        $this->collection->getBenchmarks()->willReturn(array(
            $this->benchmark,
        ));
        $this->registry->getExecutor('microtime')->willReturn($this->executor->reveal());
        $this->executor->getDefaultConfig()->willReturn(array());
    }

    /**
     * It should run the tests.
     *
     * - With 1 iteration, 1 revolution
     * - With 1 iteration, 4 revolutions
     *
     * @dataProvider provideRunner
     */
    public function testRunner($iterations, $revs, array $parameters, $xpathAssertions, $exception = null)
    {
        if ($exception) {
            $this->setExpectedException($exception[0], $exception[1]);
        }

        TestUtil::configureSubject($this->subject, array(
            'iterations' => $iterations,
            'beforeMethods' => array('beforeFoo'),
            'afterMethods' => array(),
            'parameterSets' => array(array($parameters)),
            'groups ' => array(),
            'revs' => $revs,
        ));

        TestUtil::configureBenchmark($this->benchmark);
        $this->benchmark->getSubjectMetadatas()->willReturn(array(
            $this->subject->reveal(),
        ));

        if (!$exception) {
            $this->executor->execute(Argument::type('PhpBench\Benchmark\Iteration'), array())
                ->shouldBeCalledTimes(count($revs) * $iterations)
                ->willReturn(new IterationResult(10, 10));
        }

        $result = $this->runner->run(new RunnerContext(__DIR__, array(
            'context_name' => 'context',
        )));

        $this->assertInstanceOf('PhpBench\Benchmark\SuiteDocument', $result);

        foreach ($result->query('//error') as $errorEl) {
            $this->fail('Error in suite result: ' . $errorEl->nodeValue);
        }

        foreach ($xpathAssertions as $xpathAssertion) {
            $this->assertTrue($result->evaluate($xpathAssertion), $xpathAssertion);
        }
    }

    public function provideRunner()
    {
        return array(
            array(
                1,
                1,
                array(),
                array(
                    'count(//iteration) = 1',
                ),
            ),
            array(
                1,
                3,
                array(),
                array(
                    'count(//iteration[@revs=3]) = 1',
                ),
            ),
            array(
                4,
                3,
                array(
                    'count(//iteration[@revs=3]) = 4',
                ),
                array(),
            ),
            array(
                1,
                1,
                array('one' => 'two', 'three' => 'four'),
                array(
                    'count(//parameter[@name="one"]) = 1',
                    'count(//parameter[@name="three"]) = 1',
                ),
            ),
            array(
                1,
                1,
                array('one', 'two'),
                array(
                    'count(//parameter[@name="0"]) = 1',
                    'count(//parameter[@name="1"]) = 1',
                ),
            ),
            array(
                1,
                1,
                array('one' => array('three' => 'four')),
                array(
                    'count(//parameter[@name="one"]/parameter[@name="three"]) = 1',
                ),
            ),
            array(
                1,
                1,
                array('one' => array('three' => new \stdClass())),
                array(),
                array('InvalidArgumentException', 'Parameters must be either scalars or arrays, got: stdClass'),
            ),
        );
    }

    public function nothing()
    {
        array(
        );
    }

    /**
     * It should skip subjects that should be skipped.
     */
    public function testSkip()
    {
        TestUtil::configureSubject($this->subject, array(
            'skip' => true,
        ));
        $this->benchmark->getSubjectMetadatas()->willReturn(array(
            $this->subject->reveal(),
        ));
        TestUtil::configureBenchmark($this->benchmark);
        $result = $this->runner->run(new RunnerContext(__DIR__));

        $this->assertInstanceOf('PhpBench\Benchmark\SuiteDocument', $result);
        $this->assertTrue($result->evaluate('count(//subject) = 1'));
        $this->assertTrue($result->evaluate('count(//subject/*) = 0'));
    }

    /**
     * It should set the sleep attribute in the DOM.
     * It should allow the sleep value to be overridden.
     */
    public function testSleep()
    {
        TestUtil::configureSubject($this->subject, array(
            'sleep' => 50,
        ));
        $this->benchmark->getSubjectMetadatas()->willReturn(array(
            $this->subject->reveal(),
        ));
        TestUtil::configureBenchmark($this->benchmark);
        $this->executor->execute(Argument::type('PhpBench\Benchmark\Iteration'), array())
            ->shouldBeCalledTimes(2)
            ->willReturn(new IterationResult(10, 10));

        $result = $this->runner->run(new RunnerContext(__DIR__));

        $this->assertInstanceOf('PhpBench\Benchmark\SuiteDocument', $result);
        $this->assertTrue($result->evaluate('count(//variant[@sleep="50"]) = 1'), true);

        $result = $this->runner->run(new RunnerContext(
            __DIR__,
            array(
                'sleep' => 100,
            )
        ));
        $this->assertTrue($result->evaluate('count(//variant[@sleep="100"]) = 1'), true);
    }

    /**
     * It should serialize the retry threshold.
     */
    public function testRetryThreshold()
    {
        TestUtil::configureSubject($this->subject, array(
            'sleep' => 50,
        ));
        $this->benchmark->getSubjectMetadatas()->willReturn(array(
            $this->subject->reveal(),
        ));
        TestUtil::configureBenchmark($this->benchmark);
        $this->executor->execute(Argument::type('PhpBench\Benchmark\Iteration'), array())
            ->shouldBeCalledTimes(1)
            ->willReturn(new IterationResult(10, 10));

        $result = $this->runner->run(new RunnerContext(
            __DIR__,
            array(
                'retry_threshold' => 10,
            )
        ));

        $this->assertInstanceOf('PhpBench\Benchmark\SuiteDocument', $result);
        $this->assertContains('retry-threshold="10"', $result->dump());
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
        $this->benchmark->getSubjectMetadatas()->willReturn(array());

        $this->runner->run(new RunnerContext(__DIR__));
    }

    /**
     * It should handle exceptions thrown by the executor.
     * It should handle nested exceptions.
     */
    public function testHandleExceptions()
    {
        TestUtil::configureSubject($this->subject, array(
            'sleep' => 50,
        ));
        $this->benchmark->getSubjectMetadatas()->willReturn(array(
            $this->subject->reveal(),
        ));
        TestUtil::configureBenchmark($this->benchmark);
        $this->executor->execute(Argument::type('PhpBench\Benchmark\Iteration'), array())
            ->shouldBeCalledTimes(1)
            ->willThrow(new \Exception('Foobar', null, new \InvalidArgumentException('Barfoo')));

        $result = $this->runner->run(new RunnerContext(
            __DIR__
        ));

        $this->assertTrue($result->hasErrors());
        $this->assertTrue($result->evaluate('count(//error) = 2'));
        $this->assertEquals(1, $result->evaluate('count(//error[@exception-class="Exception"])'));
        $this->assertEquals(1, $result->evaluate('count(//error[@exception-class="InvalidArgumentException"])'));
        $nodes = $result->query('//error');
        $this->assertEquals('Foobar', $nodes->item(0)->nodeValue);
        $this->assertEquals('Barfoo', $nodes->item(1)->nodeValue);
    }
}

class RunnerTestBenchCase
{
}
