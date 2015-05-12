<?php

namespace PhpBench\Result;

class IterationsResult
{
    private $iterationResults;
    private $parameters;

    public function __construct(array $iterationResults, array $parameters)
    {
        $this->iterationResults = $iterationResults;
        $this->parameters = $parameters;
    }

    public function getIterationResults() 
    {
        return $this->iterationResults;
    }

    public function getParameters() 
    {
        return $this->parameters;
    }
}
