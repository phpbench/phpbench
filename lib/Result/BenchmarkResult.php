<?php

namespace PhpBench\Result;

use PhpBench\Benchmark;

class BenchmarkResult
{
    private $subjectResults;
    private $benchmark;

    public function __construct(Benchmark $benchmark, array $subjectResults)
    {
        $this->subjectResults = $subjectResults;
        $this->benchmark = $benchmark;
    }

    public function getSubjectResults()
    {
        return $this->subjectResults;
    }

    public function getBenchmark() 
    {
        return $this->benchmark;
    }
}
