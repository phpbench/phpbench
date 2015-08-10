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

/**
 * Represents a subject that is tested by a method
 * in a benchmark class.
 */
class Subject
{
    private $methodName;
    private $beforeMethods = array();
    private $afterMethods = array();
    private $paramProviders;
    private $nbIterations;
    private $revs;
    private $groups;
    private $benchmark;

    /**
     * @param mixed $methodName
     * @param array $beforeMethods
     * @param array $afterMethods
     * @param array $paramProviders
     * @param mixed $nbIterations
     * @param array $revs
     * @param array $groups
     */
    public function __construct(
        Benchmark $benchmark,
        $methodName,
        array $beforeMethods,
        array $afterMethods,
        array $paramProviders,
        $nbIterations,
        array $revs,
        array $groups
    ) {
        $this->benchmark = $benchmark;
        $this->methodName = $methodName;
        $this->beforeMethods = $beforeMethods;
        $this->afterMethods = $afterMethods;
        $this->paramProviders = $paramProviders;
        $this->nbIterations = $nbIterations;
        $this->revs = $revs;
        $this->groups = $groups;
    }

    /**
     * Return the methods that should be executed before this subject.
     *
     * @return string[]
     */
    public function getBeforeMethods()
    {
        return $this->beforeMethods;
    }

    /**
     * Return the methods that should be executed after this subject.
     *
     * @return string[]
     */
    public function getAfterMethods()
    {
        return $this->afterMethods;
    }

    /**
     * Return the parameter provider methods for this subject.
     *
     * @return string[]
     */
    public function getParamProviders()
    {
        return $this->paramProviders;
    }

    /**
     * Return the number of iterations that should be executed
     * on the subject.
     *
     * @return int
     */
    public function getNbIterations()
    {
        return $this->nbIterations;
    }

    /**
     * Return the method in the bechmark class which this subject
     * represents.
     *
     * @return string
     */
    public function getMethodName()
    {
        return $this->methodName;
    }

    /**
     * Return the number of revolutions which should be executed.
     *
     * @return int
     */
    public function getRevs()
    {
        return $this->revs;
    }

    /**
     * Return the groups to which this subject belongs.
     *
     * @return string[]
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Return the benchmark to which this subject belong
     *
     * @return Benchmark
     */
    public function getBenchmark() 
    {
        return $this->benchmark;
    }
}
