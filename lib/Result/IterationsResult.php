<?php

namespace PhpBench\Result;

class IterationsResult
{
    private $iterationResults;
    private $parameters;

    public function __construct(array $iterationResults, array $parameters)
    {
        $this->iterationResults = $iterationResults;
        $this->parameters = $parameters;
    }

    public function getIterationResults() 
    {
        return $this->iterationResults;
    }

    public function getParameters() 
    {
        return $this->parameters;
    }

    public function getMinTime()
    {
        $min = null;

        foreach ($this->iterationResults as $iterationResult) {
            $iterationTime = $iterationResult->get('time');
            if (!$min || $iterationTime < $min) {
                $min = $iterationTime;
            }
        }

        return $min;
    }

    public function getMaxTime()
    {
        $max = null;
        foreach ($this->iterationResults as $iterationResult) {
            $iterationTime = $iterationResult->get('time');
            if (!$max || $iterationTime > $max) {
                $max = $iterationTime;
            }
        }

        return $max;
    }

    public function getTotalTime()
    {
        $total = 0;
        foreach ($this->iterationResults as $iterationResult) {
            $total += $iterationResult->get('time');
        }

        return $total;
    }

    public function getIterationCount()
    {
        return count($this->iterationResults);
    }

    public function getAverageTime()
    {
        return $this->getTotalTime() / $this->getIterationCount();
    }
}
