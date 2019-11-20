<?php

namespace PhpBench\Tests\System\benchmarks\set6;

class TimeoutBench
{
    /**
     * @Timeout(0.1)
     */
    public function benchTimeoutWillError()
    {
        usleep(200000);
    }
}
