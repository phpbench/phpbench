<?php

namespace PhpBench\Tests\Unit\Benchmark\executortest;

class ExecutorBench
{
    public function beforeMethod()
    {
        file_put_contents(__DIR__ . '/before_method.tmp', 'Before method executed');
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
