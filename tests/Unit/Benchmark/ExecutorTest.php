<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit\Benchmark;

use PhpBench\Benchmark\Executor;
use PhpBench\Benchmark\Telespector;

class ExecutorTest extends \PHPUnit_Framework_TestCase
{
    private $executor;
    private $beforeMethodFile;
    private $afterMethodFile;
    private $revFile;
    private $paramFile;
    private $paramBeforeFile;
    private $paramAfterFile;

    public function setUp()
    {
        $this->beforeMethodFile = __DIR__ . '/executortest/before_method.tmp';
        $this->afterMethodFile = __DIR__ . '/executortest/after_method.tmp';
        $this->revFile = __DIR__ . '/executortest/revs.tmp';
        $this->setupFile = __DIR__ . '/executortest/setup.tmp';
        $this->paramFile = __DIR__ . '/executortest/param.tmp';
        $this->paramBeforeFile = __DIR__ . '/executortest/parambefore.tmp';
        $this->paramAfterFile = __DIR__ . '/executortest/paramafter.tmp';
        $this->teardownFile = __DIR__ . '/executortest/teardown.tmp';

        $this->subject = $this->prophesize('PhpBench\Benchmark\Subject');
        $this->benchmark = $this->prophesize('PhpBench\Benchmark\Benchmark');

        $telespector = new Telespector(null);
        $this->executor = new Executor($telespector, null, null);
        $this->removeTemporaryFiles();

        $this->benchmark->getPath()->willReturn(__DIR__ . '/executortest/ExecutorBench.php');
        $this->benchmark->getClassFqn()->willReturn('PhpBench\Tests\Unit\Benchmark\executortest\ExecutorBench');
        $this->subject->getBenchmark()->willReturn($this->benchmark->reveal());
    }

    public function tearDown()
    {
        $this->removeTemporaryFiles();
    }

    private function removeTemporaryFiles()
    {
        foreach (array(
            $this->beforeMethodFile,
            $this->afterMethodFile,
            $this->revFile,
            $this->setupFile,
            $this->teardownFile,
            $this->paramFile,
            $this->paramBeforeFile,
            $this->paramAfterFile,
        ) as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    /**
     * It should create a script which benchmarks the code and returns
     * the time taken and the memory used.
     */
    public function testExecute()
    {
        $this->subject->getBeforeMethods()->willReturn(array());
        $this->subject->getAfterMethods()->willReturn(array());
        $this->subject->getMethodName()->willReturn('doSomething');

        $result = $this->executor->execute(
            $this->subject->reveal(),
            10,
            array()
        );

        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('time', $result);
        $this->assertArrayHasKey('memory', $result);
        $this->assertFalse(file_exists($this->beforeMethodFile));
        $this->assertFalse(file_exists($this->afterMethodFile));
        $this->assertTrue(file_exists($this->revFile));
        $this->assertEquals('10', file_get_contents($this->revFile));
    }

    /**
     * It should execute methods before the benchmark subject.
     */
    public function testExecuteBefore()
    {
        $this->subject->getBeforeMethods()->willReturn(array('beforeMethod'));
        $this->subject->getAfterMethods()->willReturn(array());
        $this->subject->getMethodName()->willReturn('doSomething');

        $this->executor->execute(
            $this->subject->reveal(),
            1,
            array()
        );

        $this->assertTrue(file_exists($this->beforeMethodFile));
    }

    /**
     * It should execute methods after the benchmark subject.
     */
    public function testExecuteAfter()
    {
        $this->subject->getBeforeMethods()->willReturn(array());
        $this->subject->getAfterMethods()->willReturn(array('afterMethod'));
        $this->subject->getMethodName()->willReturn('doSomething');

        $this->executor->execute(
            $this->subject->reveal(),
            1,
            array()
        );

        $this->assertTrue(file_exists($this->afterMethodFile));
    }

    /**
     * It should pass parameters to the benchmark method.
     */
    public function testParameters()
    {
        $this->subject->getBeforeMethods()->willReturn(array());
        $this->subject->getAfterMethods()->willReturn(array());
        $this->subject->getMethodName()->willReturn('parameterized');

        $this->executor->execute(
            $this->subject->reveal(),
            1,
            array(
                'one' => 'two',
                'three' => 'four',
            )
        );
        $this->assertTrue(file_exists($this->paramFile));
        $params = json_decode(file_get_contents($this->paramFile), true);
        $this->assertEquals(array(
            'one' => 'two',
            'three' => 'four',
        ), $params);
    }

    /**
     * It should pass parameters to the before subject and after subject methods
     */
    public function testParametersBeforeSubject()
    {
        $expected = array(
            'one' => 'two',
            'three' => 'four',
        );

        $this->subject->getBeforeMethods()->willReturn(array('parameterizedBefore'));
        $this->subject->getAfterMethods()->willReturn(array('parameterizedAfter'));
        $this->subject->getMethodName()->willReturn('parameterized');

        $this->executor->execute(
            $this->subject->reveal(),
            1,
            $expected
        );

        $this->assertTrue(file_exists($this->paramBeforeFile));
        $params = json_decode(file_get_contents($this->paramBeforeFile), true);
        $this->assertEquals($expected, $params);

        $this->assertTrue(file_exists($this->paramAfterFile));
        $params = json_decode(file_get_contents($this->paramAfterFile), true);
        $this->assertEquals($expected, $params);
    }
}
