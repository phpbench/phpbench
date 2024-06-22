<?php

namespace PhpBench\Examples\Benchmark;

use RuntimeException;

class OutputTestBench
{
    /**
     * @Assert("10ms <= 9ms +/- 1ms")
     */
    public function benchStandard()
    {
    }

    /**
     * @Assert("10ms <= 9ms")
     */
    public function benchFailure()
    {
    }

    public function benchError(): never
    {
        throw new RuntimeException("Example error");
    }
}
