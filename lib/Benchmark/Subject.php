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
    private $beforeMethods;
    private $parameters;
    private $nbIterations;
    private $processIsolation;
    private $revs;
    private $groups;
    private $identifier;

    /**
     * @param int $identifier
     * @param mixed $methodName
     * @param array $beforeMethods
     * @param array $parameters
     * @param mixed $nbIterations
     * @param array $revs
     * @param mixed $processIsolation
     * @param array $groups
     */
    public function __construct(
        $identifier,
        $methodName,
        array $beforeMethods,
        array $parameters,
        $nbIterations,
        array $revs,
        $processIsolation,
        array $groups
    ) {
        $this->identifier = $identifier;
        $this->methodName = $methodName;
        $this->beforeMethods = $beforeMethods;
        $this->parameters = $parameters;
        $this->nbIterations = $nbIterations;
        $this->revs = $revs;
        $this->processIsolation = $processIsolation;
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
     * Return the parameter provider methods for this subject.
     *
     * @return string[]
     */
    public function getParameters()
    {
        return $this->parameters;
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
     * Return the process isolation policy for this subject.
     *
     * @return string
     */
    public function getProcessIsolation()
    {
        return $this->processIsolation;
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
     * Return the identifier of this subject.
     *
     * @return int
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }
}
