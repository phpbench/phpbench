<?php

namespace PhpBench\Result;

class SuiteResult
{
    private $benchmarks;

    public function __construct(array $benchmarks)
    {
        $this->benchmarks = $benchmarks;
    }

    public function getBenchmarks() 
    {
        return $this->benchmarks;
    }
}
