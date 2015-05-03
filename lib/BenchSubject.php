<?php

namespace PhpBench;

class BenchSubject
{
    private $methodName;
    private $beforeMethods;
    private $paramProviders;
    private $nbIterations;
    private $description;

    private $iterations;

    public function __construct(
        $methodName,
        $beforeMethods,
        $paramProviders,
        $nbIterations,
        $description
    )
    {
        $this->methodName = $methodName;
        $this->beforeMethods = $beforeMethods;
        $this->paramProviders = $paramProviders;
        $this->nbIterations = $nbIterations;
        $this->description = $description;
    }

    public function getBeforeMethods() 
    {
        return $this->beforeMethods;
    }

    public function getParamProviders() 
    {
        return $this->paramProviders;
    }

    public function getNbIterations() 
    {
        return $this->iterations;
    }

    public function getDescription() 
    {
        return $this->description;
    }

    public function getMethodName()
    {
        return $this->methodName;
    }

    public function addIteration(BenchIteration $iteration)
    {
        $this->iterations[] = $iteration;
    }
}
