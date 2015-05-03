<?php

use PhpBench\BenchCase;
use PhpBench\BenchIteration;

class BenchmarkCase implements BenchCase
{
    /**
     * @description randomBench
     */
    public function benchRandom(BenchIteration $iteration)
    {
        usleep(rand(0, 50000));
    }

    /**
     * @iterations 3
     * @description Do nothing three times
     */
    public function benchDoNothing(BenchIteration $iteration)
    {
        echo $iteration->getIndex();
    }
}
