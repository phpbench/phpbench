<?php

namespace PhpBench\Result;

use PhpBench\Benchmark\Iteration;

class IterationResult
{
    private $statistics;

    public function __construct(array $statistics)
    {
        $this->statistics = $statistics;
    }

    public function getStatistics()
    {
        return $this->statistics;
    }

    public function get($statisticName)
    {
        return $this->statistics[$statisticName];
    }
}
