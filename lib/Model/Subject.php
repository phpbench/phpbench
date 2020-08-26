<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace PhpBench\Model;

use PhpBench\Util\TimeUnit;

/**
 * Subject representation.
 *
 * It represents the result rather than the details of
 * how to create that result.
 */
class Subject
{
    /**
     * @var Benchmark
     */
    private $benchmark;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string[]
     */
    private $groups = [];

    /**
     * @var int
     */
    private $sleep = 0;

    /**
     * @var float
     */
    private $retryThreshold;

    /**
     * @var string
     */
    private $outputTimeUnit = TimeUnit::MICROSECONDS;

    /**
     * @var int
     */
    private $outputTimePrecision = null;

    /**
     * @var string
     */
    private $outputMode = TimeUnit::MODE_TIME;

    /**
     * @var Variant[]
     */
    private $variants = [];

    /**
     * @var int
     */
    private $index = 0;

    /**
     * @var ResolvedExecutor
     */
    private $executor;

    /**
     * @param Benchmark $benchmark
     * @param string $name
     */
    public function __construct(Benchmark $benchmark, $name)
    {
        $this->benchmark = $benchmark;
        $this->name = $name;

        $this->index = count($benchmark->getSubjects());
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
     * @param int $revolutions
     * @param int $warmup
     *
     * @return Variant
     */
    public function createVariant(ParameterSet $parameterSet, $revolutions, $warmup, array $computedStats = [])
    {
        $variant = new Variant(
            $this,
            $parameterSet,
            $revolutions,
            $warmup,
            $computedStats
        );
        $this->variants[$parameterSet->getName()] = $variant;

        return $variant;
    }

    /**
     * @return Variant[]
     */
    public function getVariants()
    {
        return $this->variants;
    }

    /**
     * Return the (containing) benchmark for this subject.
     */
    public function getBenchmark(): Benchmark
    {
        return $this->benchmark;
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

    public function getOutputTimePrecision()
    {
        return $this->outputTimePrecision;
    }

    public function setOutputTimePrecision($outputTimePrecision)
    {
        $this->outputTimePrecision = $outputTimePrecision;
    }

    public function getOutputMode()
    {
        return $this->outputMode;
    }

    public function setOutputMode($outputMode)
    {
        $this->outputMode = $outputMode;
    }

    public function getRetryThreshold()
    {
        return $this->retryThreshold;
    }

    public function setRetryThreshold($retryThreshold)
    {
        $this->retryThreshold = $retryThreshold;
    }

    public function getIndex()
    {
        return $this->index;
    }

    /**
     * @return ResolvedExecutor|null
     */
    public function getExecutor()
    {
        return $this->executor;
    }

    public function setExecutor(ResolvedExecutor $executor)
    {
        $this->executor = $executor;
    }

    public function remove(Variant $target): void
    {
        $this->variants = array_filter($this->variants, function (Variant $variant) use ($target) {
            return $variant !== $target;
        });
    }

    public function getVariant(string $parameterSetName): ?Variant
    {
        return $this->variants[$parameterSetName] ?? null;
    }
}
