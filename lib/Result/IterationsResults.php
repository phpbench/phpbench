<?php

namespace PhpBench\Result;

class IterationsResults
{
    private $iterationsResults;

    public function __construct(array $iterationsResults)
    {
        $this->iterationsResults = $iterationsResults;
    }

    public function getIterationsResults() 
    {
        return $this->iterationsResults;
    }
}
