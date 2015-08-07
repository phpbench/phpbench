<?php

namespace PhpBench\Tests\Unit\Benchmark;

use PhpBench\Benchmark\Executor;

class ExecutorTest extends \PHPUnit_Framework_TestCase
{
    private $executor;
    private $beforeMethodFile;
    private $revFile;

    public function setUp()
    {
        $this->beforeMethodFile = __DIR__ . '/executortest/before_method.tmp';
        $this->revFile = __DIR__ . '/executortest/revs.tmp';

        $this->executor = new Executor();
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
            $this->revFile
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
            __DIR__ . '/../../../vendor/autoload.php',
            'PhpBench\Tests\Unit\Benchmark\executortest\ExecutorBench',
            'doSomething',
            10,
            array()
        );

        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('time', $result);
        $this->assertArrayHasKey('memory', $result);
        $this->assertFalse(file_exists($this->beforeMethodFile));
        $this->assertTrue(file_exists($this->revFile));
        $this->assertEquals('10', file_get_contents($this->revFile));
    }

    /**
     * It should execute methods before the benchmark subject
     */
    public function testExecuteBefore()
    {
        $this->executor->execute(
            __DIR__ . '/../../../vendor/autoload.php',
            'PhpBench\Tests\Unit\Benchmark\executortest\ExecutorBench',
            'doSomething',
            1,
            array('beforeMethod')
        );

        $this->assertTrue(file_exists($this->beforeMethodFile));
    }
}
