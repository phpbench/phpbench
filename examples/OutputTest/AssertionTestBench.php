<?php

namespace PhpBench\Examples\OutputTest;

class AssertionTestBench
{
    /**
     * @Assert("1 ms > 2 ms")
     */
    public function benchFoo(): void
    {
        usleep(1);
    }
}
