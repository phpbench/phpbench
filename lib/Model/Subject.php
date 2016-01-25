<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Model;

use PhpBench\Util\TimeUnit;

/**
 * Metadata for benchmark subjects.
 */
class Subject
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array[]
     */
    private $parameterSets = array();

    /**
     * @var BenchmarkMetadata
     */
    private $benchmark;

    /**
     * @var int
     */
    private $index;

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
     * @var int
     */
    private $warmup = 0;

    /**
     * @var bool
     */
    private $skip = false;

    /**
     * @var int
     */
    private $sleep = 0;

    /**
     * @var string
     */
    private $outputTimeUnit = null;

    /**
     * @var string
     */
    private $outputMode = TimeUnit::MODE_TIME;

    /**
     * @var Variant[]
     */
    private $variants = array();

    /**
     * @param BenchmarkMetadata $benchmark
     * @param string $name
     */
    public function __construct(Benchmark $benchmark, $name, $index)
    {
        $this->name = $name;
        $this->benchmark = $benchmark;
        $this->index = $index;
    }

    /**
     * Return the method name of this subject.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Create and add a new variant based on this subject.
     *
     * @param ParameterSet $parameterSet
     * @param int $iterationCount
     * @param int $revolutionCount
     * @param int $warmupCount
     * @param int $rejectionThreshold
     *
     * @return Variant.
     */
    public function createVariant(ParameterSet $parameterSet, $iterationCount, $revolutionCount, $warmupCount, $rejectionThreshold)
    {
        $variant = new Variant(
            $this,
            $parameterSet,
            $iterationCount,
            $revolutionCount,
            $warmupCount,
            $rejectionThreshold
        );
        $this->variants[] = $variant;

        return $variant;
    }

    public function getVariants()
    {
        return $this->variants;
    }

    /**
     * Set the parameter sets for this subject.
     *
     * @param array[] $parameterSets
     */
    public function setParameterSets(array $parameterSets)
    {
        $this->parameterSets = $parameterSets;
    }

    /**
     * Return the parameter sets for this subject.
     *
     * @return array[]
     */
    public function getParameterSets()
    {
        return $this->parameterSets;
    }

    /**
     * Return the (containing) benchmark for this subject.
     *
     * @return BenchmarkMetadata
     */
    public function getBenchmark()
    {
        return $this->benchmark;
    }

    /**
     * Return the index of the subject.
     *
     * @return int
     */
    public function getIndex()
    {
        return $this->index;
    }

    public function getGroups()
    {
        return $this->groups;
    }

    public function inGroups(array $groups)
    {
        return (bool) count(array_intersect($this->groups, $groups));
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

    public function getSleep()
    {
        return $this->sleep;
    }

    public function setSleep($sleep)
    {
        $this->sleep = $sleep;
    }

    public function getOutputTimeUnit()
    {
        return $this->outputTimeUnit;
    }

    public function setOutputTimeUnit($outputTimeUnit)
    {
        $this->outputTimeUnit = $outputTimeUnit;
    }

    public function getOutputMode()
    {
        return $this->outputMode;
    }

    public function setOutputMode($outputMode)
    {
        $this->outputMode = $outputMode;
    }

    public function getWarmup()
    {
        return $this->warmup;
    }

    public function setwarmup($warmup)
    {
        $this->warmup = $warmup;
    }
}
