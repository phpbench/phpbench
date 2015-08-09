<?php

namespace PhpBench\Tests\Unit\Benchmark\executortest;

use PhpBench\BenchmarkInterface;

class ExecutorBench implements BenchmarkInterface
{
    public function beforeMethod()
    {
        file_put_contents(__DIR__ . '/before_method.tmp', 'Before method executed');
    }

    public function afterMethod()
    {
        file_put_contents(__DIR__ . '/after_method.tmp', 'After method executed');
    }

    public function doSomething()
    {
        static $count = 0;
        $data = array();
        for ($i = 0; $i < 10000; $i++) {
            $data[] = 'hallo';
        }
        $count++;
        file_put_contents(__DIR__ . '/revs.tmp', $count);
    }
}
