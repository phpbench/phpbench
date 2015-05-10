<?php

namespace PhpBench\Result;

use PhpBench\Benchmark\Iteration;

class IterationResult
{
    private $time;
    private $memory;
    private $iteration;

    public function __construct(Iteration $iteration, $time, $memory)
    {
        $this->time = $time;
        $this->memory = $memory;
        $this->iteration = $iteration;
    }

    public function getTime() 
    {
        return $this->time;
    }

    public function getMemory() 
    {
        return $this->memory;
    }

    public function getIteration() 
    {
        return $this->iteration;
    }
}
