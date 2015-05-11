<?php

namespace PhpBench\Result;

use PhpBench\Benchmark\Iteration;

class IterationResult
{
    private $iteration;
    private $statistics;

    public function __construct(Iteration $iteration, array $statistics)
    {
        $this->statistics = $statistics;
        $this->iteration = $iteration;
    }

    public function getIteration() 
    {
        return $this->iteration;
    }

    public function getStatistics()
    {
        return $this->statistics;
    }
}
