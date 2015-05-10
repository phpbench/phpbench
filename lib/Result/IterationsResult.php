<?php

namespace PhpBench\Result;

class IterationsResult
{
    private $iterations;

    public function __construct(array $iterations)
    {
        $this->iterations = $iterations;
    }

    public function getIterations() 
    {
        return $this->iterations;
    }
}
