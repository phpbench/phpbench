<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit\Benchmark\Executor;

use PhpBench\Benchmark\Executor;
use PhpBench\Benchmark\Executor\MicrotimeExecutor;
use PhpBench\Benchmark\Remote\Launcher;
use PhpBench\Model\ParameterSet;
use PhpBench\Registry\Config;

class MicrotimeExecutorTest extends \PHPUnit_Framework_TestCase
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
        $this->beforeMethodFile = __DIR__ . '/microtimetest/before_method.tmp';
        $this->afterMethodFile = __DIR__ . '/microtimetest/after_method.tmp';
        $this->staticMethodFile = __DIR__ . '/microtimetest/static_method.tmp';
        $this->revFile = __DIR__ . '/microtimetest/revs.tmp';
        $this->setupFile = __DIR__ . '/microtimetest/setup.tmp';
        $this->paramFile = __DIR__ . '/microtimetest/param.tmp';
        $this->paramBeforeFile = __DIR__ . '/microtimetest/parambefore.tmp';
        $this->paramAfterFile = __DIR__ . '/microtimetest/paramafter.tmp';
        $this->teardownFile = __DIR__ . '/microtimetest/teardown.tmp';

        $this->metadata = $this->prophesize('PhpBench\Benchmark\Metadata\SubjectMetadata');
        $this->benchmark = $this->prophesize('PhpBench\Model\Benchmark');
        $this->benchmarkMetadata = $this->prophesize('PhpBench\Benchmark\Metadata\BenchmarkMetadata');
        $this->variant = $this->prophesize('PhpBench\Model\Variant');

        $launcher = new Launcher(null, null);
        $this->executor = new MicrotimeExecutor($launcher);
        $this->removeTemporaryFiles();

        $this->benchmarkMetadata->getPath()->willReturn(__DIR__ . '/microtimetest/ExecutorBench.php');
        $this->benchmarkMetadata->getClass()->willReturn('PhpBench\Tests\Unit\Benchmark\Executor\microtimetest\ExecutorBench');
        $this->iteration = $this->prophesize('PhpBench\Model\Iteration');
        $this->metadata->getBenchmark()->willReturn($this->benchmarkMetadata->reveal());
        $this->iteration->getVariant()->willReturn($this->variant->reveal());
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
            $this->staticMethodFile,
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
        $this->metadata->getBeforeMethods()->willReturn(array());
        $this->metadata->getAfterMethods()->willReturn(array());
        $this->metadata->getName()->willReturn('doSomething');
        $this->metadata->getRevs()->willReturn(10);
        $this->metadata->getWarmup()->willReturn(1);
        $this->variant->getParameterSet()->willReturn(new ParameterSet());

        $result = $this->executor->execute(
            $this->metadata->reveal(),
            $this->iteration->reveal(),
            new Config('test', array())
        );

        $this->assertInstanceOf('PhpBench\Model\IterationResult', $result);
        $this->assertInternalType('int', $result->getTime());
        $this->assertInternalType('int', $result->getMemory());
        $this->assertFalse(file_exists($this->beforeMethodFile));
        $this->assertFalse(file_exists($this->afterMethodFile));
        $this->assertTrue(file_exists($this->revFile));

        // 10 revolutions + 1 warmup
        $this->assertEquals('11', file_get_contents($this->revFile));
    }

    /**
     * It should prevent output from the benchmarking class.
     *
     * @expectedException RuntimeException
     * @expectedException Benchmark made some noise
     */
    public function testRepressOutput()
    {
        $this->metadata->getBeforeMethods()->willReturn(array());
        $this->metadata->getAfterMethods()->willReturn(array());
        $this->metadata->getName()->willReturn('benchOutput');
        $this->metadata->getRevs()->willReturn(10);
        $this->metadata->getWarmup()->willReturn(0);
        $this->variant->getParameterSet()->willReturn(new ParameterSet());

        $result = $this->executor->execute($this->metadata->reveal(), $this->iteration->reveal(), new Config('test', array()));

        $this->assertInstanceOf('PhpBench\Model\IterationResult', $result);
    }

    /**
     * It should execute methods before the benchmark subject.
     */
    public function testExecuteBefore()
    {
        $this->metadata->getBeforeMethods()->willReturn(array('beforeMethod'));
        $this->metadata->getAfterMethods()->willReturn(array());
        $this->metadata->getName()->willReturn('doSomething');
        $this->metadata->getRevs()->willReturn(1);
        $this->metadata->getWarmup()->willReturn(0);
        $this->variant->getParameterSet()->willReturn(new ParameterSet());

        $this->executor->execute($this->metadata->reveal(), $this->iteration->reveal(), new Config('test', array()));

        $this->assertTrue(file_exists($this->beforeMethodFile));
    }

    /**
     * It should execute methods after the benchmark subject.
     */
    public function testExecuteAfter()
    {
        $this->metadata->getBeforeMethods()->willReturn(array());
        $this->metadata->getAfterMethods()->willReturn(array('afterMethod'));
        $this->metadata->getName()->willReturn('doSomething');
        $this->metadata->getRevs()->willReturn(1);
        $this->metadata->getWarmup()->willReturn(0);
        $this->variant->getParameterSet()->willReturn(new ParameterSet());

        $this->executor->execute($this->metadata->reveal(), $this->iteration->reveal(), new Config('test', array()));

        $this->assertTrue(file_exists($this->afterMethodFile));
    }

    /**
     * It should pass parameters to the benchmark method.
     */
    public function testParameters()
    {
        $this->metadata->getBeforeMethods()->willReturn(array());
        $this->metadata->getAfterMethods()->willReturn(array());
        $this->metadata->getName()->willReturn('parameterized');

        $this->metadata->getRevs()->willReturn(1);
        $this->metadata->getWarmup()->willReturn(0);
        $this->variant->getParameterSet()->willReturn(new ParameterSet(0, array(
            'one' => 'two',
            'three' => 'four',
        )));

        $this->executor->execute($this->metadata->reveal(), $this->iteration->reveal(), new Config('test', array()));
        $this->assertTrue(file_exists($this->paramFile));
        $params = json_decode(file_get_contents($this->paramFile), true);
        $this->assertEquals(array(
            'one' => 'two',
            'three' => 'four',
        ), $params);
    }

    /**
     * It should pass parameters to the before metadata and after metadata methods.
     */
    public function testParametersBeforeSubject()
    {
        $expected = new ParameterSet(0, array(
            'one' => 'two',
            'three' => 'four',
        ));

        $this->metadata->getBeforeMethods()->willReturn(array('parameterizedBefore'));
        $this->metadata->getAfterMethods()->willReturn(array('parameterizedAfter'));
        $this->metadata->getName()->willReturn('parameterized');

        $this->metadata->getRevs()->willReturn(1);
        $this->metadata->getWarmup()->willReturn(0);
        $this->variant->getParameterSet()->willReturn($expected);

        $this->executor->execute($this->metadata->reveal(), $this->iteration->reveal(), new Config('test', array()));

        $this->assertTrue(file_exists($this->paramBeforeFile));
        $params = json_decode(file_get_contents($this->paramBeforeFile), true);
        $this->assertEquals($expected->getArrayCopy(), $params);

        $this->assertTrue(file_exists($this->paramAfterFile));
        $params = json_decode(file_get_contents($this->paramAfterFile), true);
        $this->assertEquals($expected->getArrayCopy(), $params);
    }

    /**
     * It should execute arbitrary methods on the benchmark class.
     */
    public function testExecuteMethods()
    {
        $this->executor->executeMethods($this->benchmarkMetadata->reveal(), array('initDatabase'));
        $this->assertTrue(file_exists($this->staticMethodFile));
    }
}
