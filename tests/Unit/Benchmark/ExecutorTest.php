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

class ExecutorTest extends \PHPUnit_Framework_TestCase
{
    private $executor;
    private $beforeMethodFile;
    private $afterMethodFile;
    private $revFile;
    private $paramFile;

    public function setUp()
    {
        $this->beforeMethodFile = __DIR__ . '/executortest/before_method.tmp';
        $this->afterMethodFile = __DIR__ . '/executortest/after_method.tmp';
        $this->revFile = __DIR__ . '/executortest/revs.tmp';
        $this->setupFile = __DIR__ . '/executortest/setup.tmp';
        $this->paramFile = __DIR__ . '/executortest/param.tmp';
        $this->teardownFile = __DIR__ . '/executortest/teardown.tmp';

        $this->executor = new Executor(null, null);
        $this->removeTemporaryFiles();
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
        $result = $this->executor->execute(
            new \PhpBench\Tests\Unit\Benchmark\executortest\ExecutorBench(),
            'doSomething',
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
        $this->executor->execute(
            new \PhpBench\Tests\Unit\Benchmark\executortest\ExecutorBench(),
            'doSomething',
            1,
            array('beforeMethod')
        );

        $this->assertTrue(file_exists($this->beforeMethodFile));
    }

    /**
     * It should throw an exception if a before method does not exist.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown before method "notExistingBeforeMethod" in benchmark class
     */
    public function testInvalidBeforeMethod()
    {
        $this->executor->execute(
            new \PhpBench\Tests\Unit\Benchmark\executortest\ExecutorBench(),
            'doSomething',
            1,
            array('notExistingBeforeMethod')
        );
    }

    /**
     * It should execute methods after the benchmark subject.
     */
    public function testExecuteAfter()
    {
        $this->executor->execute(
            new \PhpBench\Tests\Unit\Benchmark\executortest\ExecutorBench(),
            'doSomething',
            1,
            array(),
            array('afterMethod')
        );

        $this->assertTrue(file_exists($this->afterMethodFile));
    }

    /**
     * It should throw an exception if a after method does not exist.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown after method "notExistingAfterMethod" in benchmark class
     */
    public function testInvalidAfterMethod()
    {
        $this->executor->execute(
            new \PhpBench\Tests\Unit\Benchmark\executortest\ExecutorBench(),
            'doSomething',
            1,
            array(),
            array('notExistingAfterMethod')
        );
    }

    /**
     * It should pass parameters to the benchmark method.
     */
    public function testParameters()
    {
        $this->executor->execute(
            new \PhpBench\Tests\Unit\Benchmark\executortest\ExecutorBench(),
            'parameterized',
            1,
            array(),
            array(),
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
}
