<?php

use PhpBench\BenchmarkInterface;

/**
 * @group cost_of_calling
 * @revs 10000
 * @iterations 10
 */
class CostOfCalling implements BenchmarkInterface
{
    public function benchCallWithoutParams()
    {
        $this->doSomething();
    }

    public function benchCallWithParams()
    {
        $this->doSomethingWithParams(1, 2, 3, 4);
    }

    private function doSomething()
    {
    }

    private function doSomethingWithParams($one, $two, $three, $four)
    {
    }
}
