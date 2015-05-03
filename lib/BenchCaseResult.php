<?php

namespace PhpBench;

class BenchCaseResult
{
    private $case;
    private $subjectResults;

    public function __construct(BenchCase $case, $subjectResults)
    {
        $this->case = $case;
        $this->subjectResults = $subjectResults;
    }

    public function getCase() 
    {
        return $this->case;
    }

    public function getSubjectResults() 
    {
        return $this->subjectResults;
    }
}
