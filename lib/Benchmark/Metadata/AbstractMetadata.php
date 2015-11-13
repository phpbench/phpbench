<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Benchmark\Metadata;

/**
 * Abstract metadata class for benchmarks and subjects.
 */
abstract class AbstractMetadata
{
    /**
     * @var string[]
     */
    private $groups = array();

    /**
     * @var string[]
     */
    private $beforeMethods = array();

    /**
     * @var string[]
     */
    private $afterMethods = array();

    /**
     * @var string[]
     */
    private $paramProviders = array();

    /**
     * @var int
     */
    private $iterations = 1;

    /**
     * @var int
     */
    private $revs = 1;

    /**
     * @var string
     */
    private $class;

    /**
     * @var bool
     */
    private $skip = false;

    /**
     * @var int[]
     */
    private $concurrencies = array();

    /**
     * @param mixed $class
     */
    public function __construct($class)
    {
        $this->class = $class;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function getGroups()
    {
        return $this->groups;
    }

    public function inGroups(array $groups)
    {
        return (boolean) count(array_intersect($this->groups, $groups));
    }

    public function setGroups($groups)
    {
        $this->groups = $groups;
    }

    public function getBeforeMethods()
    {
        return $this->beforeMethods;
    }

    public function setBeforeMethods($beforeMethods)
    {
        $this->beforeMethods = $beforeMethods;
    }

    public function getAfterMethods()
    {
        return $this->afterMethods;
    }

    public function setAfterMethods($afterMethods)
    {
        $this->afterMethods = $afterMethods;
    }

    public function getParamProviders()
    {
        return $this->paramProviders;
    }

    public function setParamProviders($paramProviders)
    {
        $this->paramProviders = $paramProviders;

        return $this;
    }

    public function getIterations()
    {
        return $this->iterations;
    }

    public function setIterations($iterations)
    {
        $this->iterations = $iterations;
    }

    public function getRevs()
    {
        return $this->revs;
    }

    public function setRevs($revs)
    {
        $this->revs = $revs;
    }

    public function getSkip()
    {
        return $this->skip;
    }

    public function setSkip($skip)
    {
        $this->skip = $skip;
    }

    public function getConcurrencies()
    {
        return $this->concurrencies;
    }

    public function setConcurrencies(array $concurrencies)
    {
        $this->concurrencies = $concurrencies;
    }
}
