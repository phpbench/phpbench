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
    private $processIsolation;
    private $revs;

    public function __construct(
        $methodName,
        array $beforeMethods,
        array $parameterProviders,
        $nbIterations,
        array $revs,
        $description,
        $processIsolation
    ) {
        $this->methodName = $methodName;
        $this->beforeMethods = $beforeMethods;
        $this->parameterProviders = $parameterProviders;
        $this->nbIterations = $nbIterations;
        $this->revs = $revs;
        $this->description = $description;
        $this->processIsolation = $processIsolation;
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

    public function getProcessIsolation()
    {
        return $this->processIsolation;
    }

    public function getRevs()
    {
        return $this->revs;
    }
}
