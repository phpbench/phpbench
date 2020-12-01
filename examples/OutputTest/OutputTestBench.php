<?php

namespace PhpBench\Examples\Benchmark;

use RuntimeException;

class OutputTestBench
{
    public function benchStandard()
    {
    }

    /**
     * @Assert("10ms <= 9ms +/- 1ms")
     */
    public function benchWarning()
    {
    }

    /**
     * @Assert("10ms <= 9ms")
     */
    public function benchFailure()
    {
    }

    public function benchError()
    {
        throw new RuntimeException("Example error");
    }
}
