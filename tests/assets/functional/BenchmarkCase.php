<?php

use PhpBench\BenchCase;

class BenchmarkCase implements BenchCase
{
    /**
     * @description randomBench
     */
    public function benchRandom(BenchIteration $iteration)
    {
        usleep(rand(0, 1000000));
    }
}
