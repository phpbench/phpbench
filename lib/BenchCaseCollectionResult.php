<?php

namespace PhpBench;

use PhpBench\BenchCaseCollection;

class BenchCaseCollectionResult
{
    private $caseCollection;
    private $caseResults;

    public function __construct(BenchCaseCollection $caseCollection, array $caseResults)
    {
        $this->caseResults = $caseResults;
        $this->caseCollection = $caseCollection;
    }

    public function getCaseResults()
    {
        return $this->caseResults;
    }

    public function getCaseCollection() 
    {
        return $this->caseCollection;
    }
    
}
