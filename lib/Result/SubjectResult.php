<?php

namespace PhpBench\Result;

use PhpBench\Benchmark\Subject;

class SubjectResult
{
    private $name;
    private $description;
    private $iterationsResults;

    public function __construct($name, $description, array $iterationsResults)
    {
        $this->iterationsResults = $iterationsResults;
        $this->name = $name;
        $this->description = $description;
    }

    public function getName() 
    {
        return $this->name;
    }

    public function getDescription() 
    {
        return $this->description;
    }

    public function getIterationsResults() 
    {
        return $this->iterationsResults;
    }
}
