<?php

namespace PhpBench\Result;

class SuiteResult
{
    private $benchmarkResults;

    public function __construct(array $benchmarkResults)
    {
        $this->benchmarkResults = $benchmarkResults;
    }

    public function getBenchmarkResults() 
    {
        return $this->benchmarkResults;
    }
}
