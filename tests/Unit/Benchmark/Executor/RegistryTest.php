<?php

namespace PhpBench\Tests\Unit\Benchmark\Executor;

use PhpBench\Benchmark\Executor\Registry;

class RegistryTest extends \PHPUnit_Framework_TestCase
{
    private $registry;
    private $executor;
    private $profiler;

    public function setUp()
    {
        $this->registry = new Registry();
        $this->executor = $this->prophesize('PhpBench\Benchmark\ExecutorInterface');
        $this->profiler = $this->prophesize('PhpBench\Benchmark\ProfilerInterface');
    }

    /**
     * It should retrieve a registered executor
     */
    public function testRegisteredExcecutor()
    {
        $this->registry->register('foobar', $this->executor->reveal());
        $executor = $this->registry->getExecutor('foobar');
        $this->assertSame($this->executor->reveal(), $executor);
    }

    /**
     * It should retrieve a registered profiler
     */
    public function testRegisteredProfiler()
    {
        $this->registry->register('foobar', $this->profiler->reveal());
        $executor = $this->registry->getProfiler('foobar');
        $this->assertSame($this->profiler->reveal(), $executor);
    }

    /**
     * It should throw an exception if an executor is not mapped.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Unknown executor: "foobar", known executors: "bar"
     */
    public function testGetUnmappedExecutor()
    {
        $this->registry->register('bar', $this->executor->reveal());
        $this->registry->getExecutor('foobar');
    }

    /**
     * It should throw an exception if the requested profiler is not a profiler..
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Executor "bar" is not a profiler, registered profilers: "foobar", "barfoo"
     */
    public function testProfilerNotProfiler()
    {
        $this->registry->register('foobar', $this->profiler->reveal());
        $this->registry->register('barfoo', $this->profiler->reveal());
        $this->registry->register('bar', $this->executor->reveal());
        $this->registry->getProfiler('bar');
    }
}
