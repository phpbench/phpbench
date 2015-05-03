<?php

namespace PhpBench;

class BenchSubjectResult
{
    private $subject;
    private $iterations;

    public function __construct(BenchSubject $subject, $iterations)
    {
        $this->subject = $subject;
        $this->iterations = $iterations;
    }

    public function getSubject() 
    {
        return $this->subject;
    }

    public function getIterations() 
    {
        return $this->iterations;
    }
}
