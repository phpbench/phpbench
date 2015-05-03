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
    }


    /**
     * @paramProvider provideParamsOne
     * @paramProvider provideParamsTwo
     * @description Parameterized bench mark
     * @iterations 1
     */
    public function benchParameterized(BenchIteration $iteration)
    {
    }

    public function provideParamsOne()
    {
        return array(
            array('length' => '1'),
            array('length' => '2'),
        );
    }

    public function provideParamsTwo()
    {
        return array(
            array('strategy' => 'left'),
            array('strategy' => 'right'),
        );
    }
}
