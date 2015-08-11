<?php

namespace PhpBench\Benchmark;

class Benchmark
{
    private $path;
    private $classFqn;
    private $subjects = array();

    public function __construct($path, $classFqn)
    {
        $this->path = $path;
        $this->classFqn = $classFqn;
    }

    public function getPath() 
    {
        return $this->path;
    }

    public function getClassFqn() 
    {
        return $this->classFqn;
    }

    public function getSubjects() 
    {
        return $this->subjects;
    }

    public function addSubject(Subject $subject)
    {
        $this->subjects[] = $subject;
    }
}

