<?php

namespace PhpBench;

class BenchCaseCollection
{
    private $cases;

    public function __construct(array $cases)
    {
        $this->cases = $cases;
    }

    public function getCases()
    {
        return $this->cases;
    }
}
