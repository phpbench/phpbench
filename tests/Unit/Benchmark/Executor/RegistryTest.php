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
     * It should retrieve a registered executor.
     */
    public function testRegisteredExcecutor()
    {
        $this->registry->register('foobar', $this->executor->reveal());
        $executor = $this->registry->getExecutor('foobar');
        $this->assertSame($this->executor->reveal(), $executor);
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
}
