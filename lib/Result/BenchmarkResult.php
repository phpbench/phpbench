<?php

namespace PhpBench\Result;

use PhpBench\Benchmark;

class BenchmarkResult
{
    private $subjectResults;
    private $class;

    public function __construct($class, array $subjectResults)
    {
        $this->subjectResults = $subjectResults;
        $this->class = $class;
    }

    public function getSubjectResults()
    {
        return $this->subjectResults;
    }

    public function getClass() 
    {
        return $this->class;
    }
}
