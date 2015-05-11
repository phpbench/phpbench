<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Benchmark;

class Subject
{
    private $methodName;
    private $beforeMethods;
    private $parameterProviders;
    private $nbIterations;
    private $description;

    private $iterations;

    public function __construct(
        $methodName,
        $beforeMethods,
        $parameterProviders,
        $nbIterations,
        $description
    ) {
        $this->methodName = $methodName;
        $this->beforeMethods = $beforeMethods;
        $this->parameterProviders = $parameterProviders;
        $this->nbIterations = $nbIterations;
        $this->description = $description;
    }

    public function getBeforeMethods()
    {
        return $this->beforeMethods;
    }

    public function getParameterProviders()
    {
        return $this->parameterProviders;
    }

    public function getNbIterations()
    {
        return $this->nbIterations;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getMethodName()
    {
        return $this->methodName;
    }

    public function addIteration(Iteration $iteration)
    {
        $this->iterations[] = $iteration;
    }
}
